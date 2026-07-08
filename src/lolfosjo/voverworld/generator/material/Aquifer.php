<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\material;

use lolfosjo\voverworld\generator\densityfunction\ChunkCache;
use lolfosjo\voverworld\generator\densityfunction\ChunkCacheContext;
use lolfosjo\voverworld\generator\densityfunction\DensityFunction;
use lolfosjo\voverworld\generator\densityfunction\FunctionContext;
use lolfosjo\voverworld\util\math\Xoroshiro128;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\World;
use function abs;
use function array_key_first;
use function count;
use function floor;
use function is_nan;
use function max;
use function min;

final class Aquifer
{
    private const FLOWING_UPDATE_SIMILARITY = -0.76; // similarity(100, 144)

    private const X_RANGE = 10;
    private const Y_RANGE = 9;
    private const Z_RANGE = 10;
    private const Y_SPACING = 12;
    private const SURFACE_LEVEL_Y_OFFSET = 8;
    private const WAY_BELOW_MIN_Y = -1_000_000;

    private const SURFACE_SAMPLING_OFFSETS_IN_CHUNKS = [
        [0, 0], [-2, -1], [-1, -1], [0, -1], [1, -1], [-3, 0], [-2, 0], [-1, 0], [1, 0], [-2, 1], [-1, 1], [0, 1], [1, 1]
    ];

    private DensityFunction $barrierNoise;
    private DensityFunction $fluidLevelFloodednessNoise;
    private DensityFunction $fluidLevelSpreadNoise;
    private DensityFunction $lavaNoise;
    private DensityFunction $erosion;
    private DensityFunction $depth;
    private DensityFunction $preliminarySurfaceDensity;
    private DensityFunction $preliminarySurfaceUpperBound;

    /** @var FluidStatus[] */
    private array $aquiferCache;
    /** @var int[] */
    private array $aquiferLocationCache;
    /** @var int[] */
    private array $aquiferOffsetCache;

    private FluidPicker $globalFluidPicker;
    private int $skipSamplingAboveY;
    private int $minY;
    private int $maxY;
    private int $minGridX;
    private int $minGridY;
    private int $minGridZ;
    private int $gridSizeX;
    private int $gridSizeZ;
    private int $randomSeed;
    private int $preliminarySurfaceLowerBound;
    private int $preliminarySurfaceCellHeight;
    private CachedPointContext $cachedPointContext;
    private array $preliminarySurfaceLevelCache;
    private float $cachedBarrierNoise;
    private bool $shouldScheduleFluidUpdate;

    public function __construct(
        int $chunkX,
        int $chunkZ,
        int $seed,
        ChunkCache $chunkCache,
        DensityFunction $barrierNoise,
        DensityFunction $fluidLevelFloodednessNoise,
        DensityFunction $fluidLevelSpreadNoise,
        DensityFunction $lavaNoise,
        DensityFunction $erosion,
        DensityFunction $depth,
        DensityFunction $preliminarySurfaceDensity,
        DensityFunction $preliminarySurfaceUpperBound,
        int $preliminarySurfaceLowerBound,
        int $preliminarySurfaceCellHeight,
        int $minBlockY,
        int $yBlockSize,
        FluidPicker $globalFluidPicker
    ) {
        $this->barrierNoise = $barrierNoise;
        $this->fluidLevelFloodednessNoise = $fluidLevelFloodednessNoise;
        $this->fluidLevelSpreadNoise = $fluidLevelSpreadNoise;
        $this->lavaNoise = $lavaNoise;
        $this->erosion = $erosion;
        $this->depth = $depth;
        $this->preliminarySurfaceDensity = $preliminarySurfaceDensity;
        $this->preliminarySurfaceUpperBound = $preliminarySurfaceUpperBound;
        $this->preliminarySurfaceLowerBound = $preliminarySurfaceLowerBound;
        $this->preliminarySurfaceCellHeight = $preliminarySurfaceCellHeight;
        $this->globalFluidPicker = $globalFluidPicker;
        $this->randomSeed = $seed ^ 0x4f9939f508;
        $this->cachedPointContext = new CachedPointContext($chunkCache);
        $this->preliminarySurfaceLevelCache = [];

        $this->minY = $minBlockY;
        $this->maxY = $minBlockY + $yBlockSize - 1;

        $minBlockX = $chunkX << 4;
        $maxBlockX = $minBlockX + 15;
        $minBlockZ = $chunkZ << 4;
        $maxBlockZ = $minBlockZ + 15;

        $this->minGridX = self::gridX($minBlockX - 5);
        $maxGridX = self::gridX($maxBlockX - 5) + 1;
        $this->gridSizeX = $maxGridX - $this->minGridX + 1;
        $this->minGridY = self::gridY($minBlockY + 1) - 1;
        $maxGridY = self::gridY($minBlockY + $yBlockSize + 1) + 1;
        $gridSizeY = $maxGridY - $this->minGridY + 1;
        $this->minGridZ = self::gridZ($minBlockZ - 5);
        $maxGridZ = self::gridZ($maxBlockZ - 5) + 1;
        $this->gridSizeZ = $maxGridZ - $this->minGridZ + 1;

        $totalGridSize = $this->gridSizeX * $gridSizeY * $this->gridSizeZ;
        $this->aquiferCache = array_fill(0, $totalGridSize, null);
        $this->aquiferLocationCache = array_fill(0, $totalGridSize, 0);
        $this->aquiferOffsetCache = array_fill(0, $totalGridSize, 0);

        $this->preloadAquiferLocations($gridSizeY);

        $maxAdjustedSurfaceLevel = $this->adjustSurfaceLevel(
            $this->maxPreliminarySurfaceLevel(
                self::fromGridX($this->minGridX, 0),
                self::fromGridZ($this->minGridZ, 0),
                self::fromGridX($maxGridX, 9),
                self::fromGridZ($maxGridZ, 9)
            )
        );
        $skipSamplingAboveGridY = self::gridY($maxAdjustedSurfaceLevel + 12) + 1;
        $this->skipSamplingAboveY = self::fromGridY($skipSamplingAboveGridY, 11) - 1;
    }

    public function computeSubstance(FunctionContext $context, float $density): ?Block
    {
        if ($density > 0.0) {
            $this->shouldScheduleFluidUpdate = false;
            return null;
        }

        $posX = $context->blockX();
        $posY = $context->blockY();
        $posZ = $context->blockZ();
        $globalFluid = $this->globalFluidPicker->computeFluid($posX, $posY, $posZ);
        $globalAtPos = $globalFluid->at($posY);

        if ($posY > $this->skipSamplingAboveY) {
            $this->shouldScheduleFluidUpdate = false;
            return $globalAtPos;
        }

        if ($this->isLava($globalAtPos)) {
            $this->shouldScheduleFluidUpdate = false;
            return VanillaBlocks::LAVA();
        }

        $xAnchor = self::gridX($posX - 5);
        $yAnchor = self::gridY($posY + 1);
        $zAnchor = self::gridZ($posZ - 5);

        $distanceSqr1 = PHP_INT_MAX;
        $distanceSqr2 = PHP_INT_MAX;
        $distanceSqr3 = PHP_INT_MAX;
        $distanceSqr4 = PHP_INT_MAX;
        $closestIndex1 = 0;
        $closestIndex2 = 0;
        $closestIndex3 = 0;
        $closestIndex4 = 0;

        for ($x1 = 0; $x1 <= 1; $x1++) {
            for ($y1 = -1; $y1 <= 1; $y1++) {
                for ($z1 = 0; $z1 <= 1; $z1++) {
                    $spacedGridX = $xAnchor + $x1;
                    $spacedGridY = $yAnchor + $y1;
                    $spacedGridZ = $zAnchor + $z1;
                    $index = $this->getIndex($spacedGridX, $spacedGridY, $spacedGridZ);
                    $packedOffset = $this->aquiferOffsetCache[$index];
                    $dx = self::fromGridX($spacedGridX, self::unpackOffsetX($packedOffset)) - $posX;
                    $dy = self::fromGridY($spacedGridY, self::unpackOffsetY($packedOffset)) - $posY;
                    $dz = self::fromGridZ($spacedGridZ, self::unpackOffsetZ($packedOffset)) - $posZ;
                    $newDistance = $dx * $dx + $dy * $dy + $dz * $dz;
                    if ($distanceSqr1 >= $newDistance) {
                        $closestIndex4 = $closestIndex3;
                        $closestIndex3 = $closestIndex2;
                        $closestIndex2 = $closestIndex1;
                        $closestIndex1 = $index;
                        $distanceSqr4 = $distanceSqr3;
                        $distanceSqr3 = $distanceSqr2;
                        $distanceSqr2 = $distanceSqr1;
                        $distanceSqr1 = $newDistance;
                    } elseif ($distanceSqr2 >= $newDistance) {
                        $closestIndex4 = $closestIndex3;
                        $closestIndex3 = $closestIndex2;
                        $closestIndex2 = $index;
                        $distanceSqr4 = $distanceSqr3;
                        $distanceSqr3 = $distanceSqr2;
                        $distanceSqr2 = $newDistance;
                    } elseif ($distanceSqr3 >= $newDistance) {
                        $closestIndex4 = $closestIndex3;
                        $closestIndex3 = $index;
                        $distanceSqr4 = $distanceSqr3;
                        $distanceSqr3 = $newDistance;
                    } elseif ($distanceSqr4 >= $newDistance) {
                        $closestIndex4 = $index;
                        $distanceSqr4 = $newDistance;
                    }
                }
            }
        }

        $closestStatus1 = $this->getAquiferStatus($closestIndex1);
        $similarity12 = self::similarity($distanceSqr1, $distanceSqr2);
        $fluidState = $closestStatus1->at($posY);
        if ($similarity12 <= 0.0) {
            if ($similarity12 >= self::FLOWING_UPDATE_SIMILARITY) {
                $closestStatus2 = $this->getAquiferStatus($closestIndex2);
                $this->shouldScheduleFluidUpdate = !$closestStatus1->equals($closestStatus2);
            } else {
                $this->shouldScheduleFluidUpdate = false;
            }
            return $fluidState;
        }

        if ($this->isWater($fluidState) && $this->isLava($this->globalFluidPicker->computeFluid($posX, $posY - 1, $posZ)->at($posY - 1))) {
            $this->shouldScheduleFluidUpdate = true;
            return $fluidState;
        }

        $this->cachedBarrierNoise = NAN;
        $closestStatus2 = $this->getAquiferStatus($closestIndex2);
        $barrier12 = $similarity12 * $this->calculatePressure($context, $closestStatus1, $closestStatus2);
        if ($density + $barrier12 > 0.0) {
            $this->shouldScheduleFluidUpdate = false;
            return VanillaBlocks::STONE();
        }

        $closestStatus3 = $this->getAquiferStatus($closestIndex3);
        $similarity13 = self::similarity($distanceSqr1, $distanceSqr3);
        if ($similarity13 > 0.0) {
            $barrier13 = $similarity12 * $similarity13 * $this->calculatePressure($context, $closestStatus1, $closestStatus3);
            if ($density + $barrier13 > 0.0) {
                $this->shouldScheduleFluidUpdate = false;
                return VanillaBlocks::STONE();
            }
        }

        $similarity23 = self::similarity($distanceSqr2, $distanceSqr3);
        if ($similarity23 > 0.0) {
            $barrier23 = $similarity12 * $similarity23 * $this->calculatePressure($context, $closestStatus2, $closestStatus3);
            if ($density + $barrier23 > 0.0) {
                $this->shouldScheduleFluidUpdate = false;
                return VanillaBlocks::STONE();
            }
        }

        $mayFlow12 = !$closestStatus1->equals($closestStatus2);
        $mayFlow23 = $similarity23 >= self::FLOWING_UPDATE_SIMILARITY && !$closestStatus2->equals($closestStatus3);
        $mayFlow13 = $similarity13 >= self::FLOWING_UPDATE_SIMILARITY && !$closestStatus1->equals($closestStatus3);
        if (!$mayFlow12 && !$mayFlow23 && !$mayFlow13) {
            $this->shouldScheduleFluidUpdate = $similarity13 >= self::FLOWING_UPDATE_SIMILARITY
                && self::similarity($distanceSqr1, $distanceSqr4) >= self::FLOWING_UPDATE_SIMILARITY
                && !$closestStatus1->equals($this->getAquiferStatus($closestIndex4));
        } else {
            $this->shouldScheduleFluidUpdate = true;
        }

        return $fluidState;
    }

    public function shouldScheduleFluidUpdate(): bool
    {
        return $this->shouldScheduleFluidUpdate;
    }

    private function getIndex(int $gridX, int $gridY, int $gridZ): int
    {
        $x = $gridX - $this->minGridX;
        $y = $gridY - $this->minGridY;
        $z = $gridZ - $this->minGridZ;
        return ($y * $this->gridSizeZ + $z) * $this->gridSizeX + $x;
    }

    private function preloadAquiferLocations(int $gridSizeY): void
    {
        $random = new Xoroshiro128();
        for ($y = 0; $y < $gridSizeY; $y++) {
            $gridY = $this->minGridY + $y;
            for ($z = 0; $z < $this->gridSizeZ; $z++) {
                $gridZ = $this->minGridZ + $z;
                for ($x = 0; $x < $this->gridSizeX; $x++) {
                    $gridX = $this->minGridX + $x;
                    $random->setSeed(self::mixSeed($this->randomSeed, $gridX, $gridY, $gridZ));
                    $offsetX = $random->nextInt(self::X_RANGE);
                    $offsetY = $random->nextInt(self::Y_RANGE);
                    $offsetZ = $random->nextInt(self::Z_RANGE);
                    $index = ($y * $this->gridSizeZ + $z) * $this->gridSizeX + $x;
                    $this->aquiferOffsetCache[$index] = self::packOffset($offsetX, $offsetY, $offsetZ);
                    $this->aquiferLocationCache[$index] = self::pack(
                        self::fromGridX($gridX, $offsetX),
                        self::fromGridY($gridY, $offsetY),
                        self::fromGridZ($gridZ, $offsetZ)
                    );
                }
            }
        }
    }

    private function calculatePressure(
        FunctionContext $context,
        FluidStatus $statusClosest1,
        FluidStatus $statusClosest2
    ): float {
        $posY = $context->blockY();
        $type1 = $statusClosest1->at($posY);
        $type2 = $statusClosest2->at($posY);
        if ((!$this->isLava($type1) || !$this->isWater($type2)) && (!$this->isWater($type1) || !$this->isLava($type2))) {
            $fluidYDiff = abs($statusClosest1->fluidLevel - $statusClosest2->fluidLevel);
            if ($fluidYDiff == 0) {
                return 0.0;
            }

            $averageFluidY = 0.5 * ($statusClosest1->fluidLevel + $statusClosest2->fluidLevel);
            $howFarAboveAverageFluidPoint = $posY + 0.5 - $averageFluidY;
            $baseValue = $fluidYDiff / 2.0;
            $topBias = 0.0;
            $furthestRocksFromTopBias = 2.5;
            $furthestHolesFromTopBias = 1.5;
            $bottomBias = 3.0;
            $furthestRocksFromBottomBias = 10.0;
            $furthestHolesFromBottomBias = 3.0;
            $distanceFromBarrierEdgeTowardsMiddle = $baseValue - abs($howFarAboveAverageFluidPoint);
            if ($howFarAboveAverageFluidPoint > 0.0) {
                $centerPoint = $topBias + $distanceFromBarrierEdgeTowardsMiddle;
                if ($centerPoint > 0.0) {
                    $gradient = $centerPoint / $furthestHolesFromTopBias;
                } else {
                    $gradient = $centerPoint / $furthestRocksFromTopBias;
                }
            } else {
                $centerPoint = $bottomBias + $distanceFromBarrierEdgeTowardsMiddle;
                if ($centerPoint > 0.0) {
                    $gradient = $centerPoint / $furthestHolesFromBottomBias;
                } else {
                    $gradient = $centerPoint / $furthestRocksFromBottomBias;
                }
            }

            $amplitude = 2.0;
            if ($gradient >= -$amplitude && $gradient <= $amplitude) {
                $currentNoiseValue = $this->cachedBarrierNoise;
                if (is_nan($currentNoiseValue)) {
                    $noiseValue = $this->barrierNoise->compute($context);
                    $this->cachedBarrierNoise = $noiseValue;
                } else {
                    $noiseValue = $currentNoiseValue;
                }
            } else {
                $noiseValue = 0.0;
            }

            return $amplitude * ($noiseValue + $gradient);
        }
        return 2.0;
    }

    private function getAquiferStatus(int $index): FluidStatus
    {
        $oldStatus = $this->aquiferCache[$index];
        if ($oldStatus !== null) {
            return $oldStatus;
        }
        $location = $this->aquiferLocationCache[$index];
        $status = $this->computeFluid(
            self::unpackX($location),
            self::unpackY($location),
            self::unpackZ($location)
        );
        $this->aquiferCache[$index] = $status;
        return $status;
    }

    private function computeFluid(int $x, int $y, int $z): FluidStatus
    {
        $globalFluid = $this->globalFluidPicker->computeFluid($x, $y, $z);
        $lowestPreliminarySurface = PHP_INT_MAX;
        $topOfAquiferCell = $y + self::Y_SPACING;
        $bottomOfAquiferCell = $y - self::Y_SPACING;
        $surfaceAtCenterIsUnderGlobalFluidLevel = false;

        foreach (self::SURFACE_SAMPLING_OFFSETS_IN_CHUNKS as $offset) {
            $sampleX = $x + ($offset[0] << 4);
            $sampleZ = $z + ($offset[1] << 4);
            $preliminarySurfaceLevel = $this->preliminarySurfaceLevel($sampleX, $sampleZ);
            $adjustedSurfaceLevel = $this->adjustSurfaceLevel($preliminarySurfaceLevel);
            $start = $offset[0] == 0 && $offset[1] == 0;
            if ($start && $bottomOfAquiferCell > $adjustedSurfaceLevel) {
                return $globalFluid;
            }

            $topOfAquiferCellPokesAboveSurface = $topOfAquiferCell > $adjustedSurfaceLevel;
            if ($topOfAquiferCellPokesAboveSurface || $start) {
                $globalFluidAtSurface = $this->globalFluidPicker->computeFluid($sampleX, $adjustedSurfaceLevel, $sampleZ);
                if (!$this->isAir($globalFluidAtSurface->at($adjustedSurfaceLevel))) {
                    if ($start) {
                        $surfaceAtCenterIsUnderGlobalFluidLevel = true;
                    }
                    if ($topOfAquiferCellPokesAboveSurface) {
                        return $globalFluidAtSurface;
                    }
                }
            }
            $lowestPreliminarySurface = min($lowestPreliminarySurface, $preliminarySurfaceLevel);
        }

        $fluidSurfaceLevel = $this->computeSurfaceLevel($x, $y, $z, $globalFluid, $lowestPreliminarySurface, $surfaceAtCenterIsUnderGlobalFluidLevel);
        return new FluidStatus($fluidSurfaceLevel, $this->computeFluidType($x, $y, $z, $globalFluid, $fluidSurfaceLevel));
    }

    private function adjustSurfaceLevel(int $preliminarySurfaceLevel): int
    {
        return $preliminarySurfaceLevel + self::SURFACE_LEVEL_Y_OFFSET;
    }

    private function computeSurfaceLevel(
        int $x,
        int $y,
        int $z,
        FluidStatus $globalFluid,
        int $lowestPreliminarySurface,
        bool $surfaceAtCenterIsUnderGlobalFluidLevel
    ): int {
        $context = $this->cachedPointContext->set($x, $y, $z);
        if ($this->isDeepDarkRegion($this->erosion, $this->depth, $context)) {
            $partiallyFloodedness = -1.0;
            $fullyFloodedness = -1.0;
        } else {
            $distanceBelowSurface = $lowestPreliminarySurface + self::SURFACE_LEVEL_Y_OFFSET - $y;
            $floodednessFactor = $surfaceAtCenterIsUnderGlobalFluidLevel ? self::clampedMap($distanceBelowSurface, 0.0, 64.0, 1.0, 0.0) : 0.0;
            $floodednessNoiseValue = min(1.0, max(-1.0, $this->fluidLevelFloodednessNoise->compute($context)));
            $fullyFloodedThreshold = self::map($floodednessFactor, 1.0, 0.0, -0.3, 0.8);
            $partiallyFloodedThreshold = self::map($floodednessFactor, 1.0, 0.0, -0.8, 0.4);
            $partiallyFloodedness = $floodednessNoiseValue - $partiallyFloodedThreshold;
            $fullyFloodedness = $floodednessNoiseValue - $fullyFloodedThreshold;
        }

        if ($fullyFloodedness > 0.0) {
            return $globalFluid->fluidLevel;
        }
        if ($partiallyFloodedness > 0.0) {
            return $this->computeRandomizedFluidSurfaceLevel($x, $y, $z, $lowestPreliminarySurface);
        }
        return self::WAY_BELOW_MIN_Y;
    }

    private function computeRandomizedFluidSurfaceLevel(int $x, int $y, int $z, int $lowestPreliminarySurface): int
    {
        $fluidLevelCellX = (int) floor($x / 16);
        $fluidLevelCellY = (int) floor($y / 40);
        $fluidLevelCellZ = (int) floor($z / 16);
        $fluidCellMiddleY = $fluidLevelCellY * 40 + 20;
        $fluidLevelSpread = $this->fluidLevelSpreadNoise
            ->compute($this->cachedPointContext->set($fluidLevelCellX, $fluidLevelCellY, $fluidLevelCellZ))
            * 10.0;
        $fluidLevelSpreadQuantized = self::quantize($fluidLevelSpread, 3);
        $targetFluidSurfaceLevel = $fluidCellMiddleY + $fluidLevelSpreadQuantized;
        return min($lowestPreliminarySurface, $targetFluidSurfaceLevel);
    }

    private function computeFluidType(int $x, int $y, int $z, FluidStatus $globalFluid, int $fluidSurfaceLevel): Block
    {
        $fluidType = $globalFluid->fluidType;
        if ($fluidSurfaceLevel <= -10 && $fluidSurfaceLevel != self::WAY_BELOW_MIN_Y && !$this->isLava($globalFluid->fluidType)) {
            $fluidTypeCellX = (int) floor($x / 64);
            $fluidTypeCellY = (int) floor($y / 40);
            $fluidTypeCellZ = (int) floor($z / 64);
            $lavaNoiseValue = $this->lavaNoise->compute($this->cachedPointContext->set($fluidTypeCellX, $fluidTypeCellY, $fluidTypeCellZ));
            if (abs($lavaNoiseValue) > 0.3) {
                $fluidType = VanillaBlocks::LAVA();
            }
        }
        return $fluidType;
    }

    private function preliminarySurfaceLevel(int $worldX, int $worldZ): int
    {
        $key = (($worldX << 32) ^ ($worldZ & 0xFFFFFFFF));
        if (isset($this->preliminarySurfaceLevelCache[$key])) {
            // LRU: Move entry to the end (mark as most recently used)
            $value = $this->preliminarySurfaceLevelCache[$key];
            unset($this->preliminarySurfaceLevelCache[$key]);
            $this->preliminarySurfaceLevelCache[$key] = $value;
            return $value;
        }

        $lowerY = max($this->minY, $this->preliminarySurfaceLowerBound);
        $upperY = (int) floor($this->preliminarySurfaceUpperBound->compute($this->cachedPointContext->set($worldX, 0, $worldZ)));
        $upperY = min($this->maxY, (int) (floor($upperY / $this->preliminarySurfaceCellHeight) * $this->preliminarySurfaceCellHeight));

        $result = $lowerY;
        if ($upperY > $lowerY) {
            for ($y = $upperY; $y >= $lowerY; $y -= $this->preliminarySurfaceCellHeight) {
                if ($this->preliminarySurfaceDensity->compute($this->cachedPointContext->set($worldX, $y, $worldZ)) > 0.0) {
                    $result = $y;
                    break;
                }
            }
        }
        $this->putPreliminarySurfaceCache($key, $result);
        return $result;
    }

    private function putPreliminarySurfaceCache(int $key, int $value): void
    {
        // New entry is added to the end (most recent entry)
        $this->preliminarySurfaceLevelCache[$key] = $value;
        // If capacity is exceeded: remove the oldest entry (at the front)
        if (count($this->preliminarySurfaceLevelCache) > 64) {
            $firstKey = array_key_first($this->preliminarySurfaceLevelCache);
            if ($firstKey !== null) {
                unset($this->preliminarySurfaceLevelCache[$firstKey]);
            }
        }
    }

    private function maxPreliminarySurfaceLevel(int $minX, int $minZ, int $maxX, int $maxZ): int
    {
        $maxSurface = PHP_INT_MIN;
        for ($x = $minX; $x <= $maxX; $x += 4) {
            for ($z = $minZ; $z <= $maxZ; $z += 4) {
                $maxSurface = max($maxSurface, $this->preliminarySurfaceLevel($x, $z));
            }
        }
        return $maxSurface === PHP_INT_MIN ? 63 : $maxSurface;
    }

    private static function gridX(int $blockCoord): int
    {
        return $blockCoord >> 4;
    }

    private static function fromGridX(int $gridCoord, int $blockOffset): int
    {
        return ($gridCoord << 4) + $blockOffset;
    }

    private static function gridY(int $blockCoord): int
    {
        return (int) floor($blockCoord / 12);
    }

    private static function fromGridY(int $gridCoord, int $blockOffset): int
    {
        return $gridCoord * 12 + $blockOffset;
    }

    private static function gridZ(int $blockCoord): int
    {
        return $blockCoord >> 4;
    }

    private static function fromGridZ(int $gridCoord, int $blockOffset): int
    {
        return ($gridCoord << 4) + $blockOffset;
    }

    private static function pack(int $x, int $y, int $z): int
    {
        return ((($x & 0x3FFFFFF) << 38) | (($z & 0x3FFFFFF) << 12) | ($y & 0xFFF));
    }

    private static function packOffset(int $x, int $y, int $z): int
    {
        return (($x << 8) | ($y << 4) | $z);
    }

    private static function unpackOffsetX(int $packed): int
    {
        return ($packed >> 8) & 0xF;
    }

    private static function unpackOffsetY(int $packed): int
    {
        return ($packed >> 4) & 0xF;
    }

    private static function unpackOffsetZ(int $packed): int
    {
        return $packed & 0xF;
    }

    private static function unpackX(int $packed): int
    {
        return $packed >> 38;
    }

    private static function unpackY(int $packed): int
    {
        // 12-bit signed: arithmetic shift (sign extension like Java's << 52 >> 52)
        return ($packed << 52) >> 52;
    }

    private static function unpackZ(int $packed): int
    {
        // 26-bit signed: arithmetic shift (sign extension like Java's << 26 >> 38)
        return ($packed << 26) >> 38;
    }

    private function isDeepDarkRegion(DensityFunction $erosion, DensityFunction $depth, FunctionContext $context): bool
    {
        return $erosion->compute($context) < -0.225 && $depth->compute($context) > 0.9;
    }

    private static function similarity(int $distanceSqr1, int $distanceSqr2): float
    {
        return 1.0 - ($distanceSqr2 - $distanceSqr1) / 25.0;
    }

    private static function clampedMap(float $value, float $inMin, float $inMax, float $outMin, float $outMax): float
    {
        if ($inMin == $inMax) {
            return $value < $inMin ? $outMin : $outMax;
        }
        $t = max(0.0, min(1.0, ($value - $inMin) / ($inMax - $inMin)));
        return $outMin + ($outMax - $outMin) * $t;
    }

    private static function map(float $value, float $inMin, float $inMax, float $outMin, float $outMax): float
    {
        if ($inMin == $inMax) {
            return $outMin;
        }
        $t = ($value - $inMin) / ($inMax - $inMin);
        return $outMin + ($outMax - $outMin) * $t;
    }

    private static function quantize(float $value, int $step): int
    {
        return (int) (floor($value / $step) * $step);
    }

    private static function mixSeed(int $seed, int $x, int $y, int $z): int
    {
        $mask = gmp_init('0xFFFFFFFFFFFFFFFF', 16);
        $pow2_64 = gmp_pow(2, 64);
        $pow2_63 = gmp_pow(2, 63);
        $pow2_33 = gmp_pow(2, 33);

        if ($seed < 0) {
            $mixed = gmp_and(gmp_add(gmp_init($seed), $pow2_64), $mask);
        } else {
            $mixed = gmp_and(gmp_init($seed), $mask);
        }

        $mixed = gmp_and(gmp_xor($mixed, gmp_mul(gmp_init($x), gmp_init(341873128712))), $mask);
        $mixed = gmp_and(gmp_xor($mixed, gmp_mul(gmp_init($y), gmp_init(132897987541))), $mask);
        $mixed = gmp_and(gmp_xor($mixed, gmp_mul(gmp_init($z), gmp_init(42317861))), $mask);

        $mixed = gmp_and(gmp_xor($mixed, gmp_div_q($mixed, $pow2_33)), $mask);
        $mixed = gmp_and(gmp_mul($mixed, gmp_init('0xff51afd7ed558ccd', 16)), $mask);
        $mixed = gmp_and(gmp_xor($mixed, gmp_div_q($mixed, $pow2_33)), $mask);
        $mixed = gmp_and(gmp_mul($mixed, gmp_init('0xc4ceb9fe1a85ec53', 16)), $mask);
        $mixed = gmp_and(gmp_xor($mixed, gmp_div_q($mixed, $pow2_33)), $mask);

        if (gmp_cmp($mixed, $pow2_63) >= 0) {
            $mixed = gmp_sub($mixed, $pow2_64);
        }
        return gmp_intval($mixed);
    }

    private function isLava(Block $block): bool
    {
        return $block->getTypeId() === VanillaBlocks::LAVA()->getTypeId();
    }

    private function isWater(Block $block): bool
    {
        return $block->getTypeId() === VanillaBlocks::WATER()->getTypeId();
    }

    private function isAir(Block $block): bool
    {
        return $block->getTypeId() === VanillaBlocks::AIR()->getTypeId();
    }

    public static function overworldFluidPicker(int $seaLevel): FluidPicker
    {
        $water = VanillaBlocks::WATER();
        $lava = VanillaBlocks::LAVA();
        $lavaLevel = -54;
        $lavaThreshold = min($lavaLevel, $seaLevel);
        return new class($lavaThreshold, $lavaLevel, $lava, $seaLevel, $water) implements FluidPicker {
            private int $lavaThreshold;
            private int $lavaLevel;
            private Block $lava;
            private int $seaLevel;
            private Block $water;

            public function __construct(int $lavaThreshold, int $lavaLevel, Block $lava, int $seaLevel, Block $water)
            {
                $this->lavaThreshold = $lavaThreshold;
                $this->lavaLevel = $lavaLevel;
                $this->lava = $lava;
                $this->seaLevel = $seaLevel;
                $this->water = $water;
            }

            public function computeFluid(int $blockX, int $blockY, int $blockZ): FluidStatus
            {
                if ($blockY < $this->lavaThreshold) {
                    return new FluidStatus($this->lavaLevel, $this->lava);
                }
                return new FluidStatus($this->seaLevel, $this->water);
            }
        };
    }
}

final class FluidStatus
{
    public int $fluidLevel;
    public Block $fluidType;

    public function __construct(int $fluidLevel, Block $fluidType)
    {
        $this->fluidLevel = $fluidLevel;
        $this->fluidType = $fluidType;
    }

    public function at(int $blockY): Block
    {
        return $blockY < $this->fluidLevel ? $this->fluidType : VanillaBlocks::AIR();
    }

    public function equals(FluidStatus $other): bool
    {
        return $this->fluidLevel === $other->fluidLevel && $this->fluidType->getTypeId() === $other->fluidType->getTypeId();
    }
}

interface FluidPicker
{
    public function computeFluid(int $blockX, int $blockY, int $blockZ): FluidStatus;
}

final class CachedPointContext implements ChunkCacheContext
{
    private ChunkCache $chunkCache;
    private int $blockX;
    private int $blockY;
    private int $blockZ;

    public function __construct(ChunkCache $chunkCache)
    {
        $this->chunkCache = $chunkCache;
    }

    public function set(int $blockX, int $blockY, int $blockZ): self
    {
        $this->blockX = $blockX;
        $this->blockY = $blockY;
        $this->blockZ = $blockZ;
        return $this;
    }

    public function blockX(): int
    {
        return $this->blockX;
    }

    public function blockY(): int
    {
        return $this->blockY;
    }

    public function blockZ(): int
    {
        return $this->blockZ;
    }

    public function densityChunkCache(): ChunkCache
    {
        return $this->chunkCache;
    }
}