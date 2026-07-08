<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator;

use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\format\Chunk;
use lolfosjo\voverworld\generator\holder\NormalObjectHolder;
use lolfosjo\voverworld\generator\densityfunction\DensityCommon;
use lolfosjo\voverworld\generator\densityfunction\CellFunctionContext;
use lolfosjo\voverworld\util\math\Xoroshiro128;
use lolfosjo\voverworld\generator\densityfunction\OverworldCavesDensity;
use pocketmine\utils\Random;
use lolfosjo\voverworld\generator\material\MultiMaterial;

class TestGenerator extends Generator
{
    protected int $seed;
    private NormalObjectHolder $holder;
    private Random $biomeRandom;
    private int $minY = -64;
    private int $maxY = 319;
    private const CELL_XZ_SIZE = 4;
    private const CELL_HEIGHT  = 8;
    private const CELL_X_COUNT = 4;
    private const CELL_Z_COUNT = 4;
    private const SEA_LEVEL = 63;
    private const CORNER_FLOOD_SEED_MAX_Y = 192;

    public function __construct(int $seed, string $preset = '')
    {
        parent::__construct($seed, $preset);
        $this->seed = $seed;
        $random = new Xoroshiro128($seed);
        $this->holder = new NormalObjectHolder($random);
        $this->biomeRandom = new Random($seed);
        
    }

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if ($chunk === null) {
            return;
        }

        $this->holder->getFlatCacheMarker()->invalidate();
        $terrain       = $this->holder->getTerrainHolder();
        $multiMaterial = $terrain->getMultiMaterial();

        if ($multiMaterial === null) {
            throw new \RuntimeException("MultiMaterial not initialized");
        }

        $minY      = $this->minY;
        $maxY      = $this->maxY;
        $chunkBaseX = $chunkX << 4;
        $chunkBaseZ = $chunkZ << 4;

        $cellMinY = (int) floor($minY / self::CELL_HEIGHT) * self::CELL_HEIGHT;
        $cellMaxY = (int) floor($maxY / self::CELL_HEIGHT) * self::CELL_HEIGHT;
        $cellYCount = (int) (($cellMaxY - $cellMinY) / self::CELL_HEIGHT) + 1;

        $chunkCache      = DensityCommon::chunkCache($chunk);
        $functionContext = new CellFunctionContext($chunkCache);
        $offset = $terrain->getOffset();
        $factor = $terrain->getFactor();

        $mandatoryTopY = [];

        $terrain->beginAquifer(
    $chunkX,
    $chunkZ,
    $this->seed,
    $chunkCache,
    $minY,
    $maxY - $minY + 1,
    self::SEA_LEVEL
);

        $prelimUpper   = OverworldCavesDensity::preliminarySurfaceLevelUpperBound($offset,  $factor);
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $worldX = $chunkBaseX + $x;
                $worldZ = $chunkBaseZ + $z;
                $upper  = (int) ceil($prelimUpper->compute($functionContext->set($worldX, 0, $worldZ)));
                $mandatoryTopY[$x][$z] = min($maxY, max(self::SEA_LEVEL, $upper));
            }
        }

        $queued             = array_fill(0, self::CELL_X_COUNT * $cellYCount * self::CELL_Z_COUNT, false);
        $solidMandatoryCells = array_fill(0, count($queued), false);
        $queue              = new \SplQueue();

        for ($cellYIndex = 0; $cellYIndex < $cellYCount; $cellYIndex++) {
            $cellY = $cellMinY + $cellYIndex * self::CELL_HEIGHT;
            for ($cellXIndex = 0; $cellXIndex < self::CELL_X_COUNT; $cellXIndex++) {
                for ($cellZIndex = 0; $cellZIndex < self::CELL_Z_COUNT; $cellZIndex++) {
                    $cellX = $cellXIndex * self::CELL_XZ_SIZE;
                    $cellZ = $cellZIndex * self::CELL_XZ_SIZE;

                    if (!$this->shouldGenerateMandatoryCell($mandatoryTopY, $cellX, $cellY, $cellZ)
                        && !$this->isCornerFloodSeedCell($cellXIndex, $cellYIndex, $cellY, $cellZIndex)
                    ) {
                        continue;
                    }

                    $cellIndex = $this->cellIndex($cellXIndex, $cellYIndex, $cellZIndex);
                    $queued[$cellIndex] = true;

                    $solidMandatoryCells[$cellIndex] = $this->generateCell(
                        $chunk,
                        $multiMaterial,
                        $functionContext,
                        $chunkBaseX,
                        $chunkBaseZ,
                        $minY,
                        $maxY,
                        $cellX,
                        $cellY,
                        $cellZ
                    );
                }
            }
        }

        for ($cellIndex = 0; $cellIndex < count($solidMandatoryCells); $cellIndex++) {
            if (!$solidMandatoryCells[$cellIndex]) {
                continue;
            }
            $cellXIndex = $cellIndex % self::CELL_X_COUNT;
            $cellZIndex = (int) ($cellIndex / self::CELL_X_COUNT) % self::CELL_Z_COUNT;
            $cellYIndex = (int) ($cellIndex / (self::CELL_X_COUNT * self::CELL_Z_COUNT));
            $this->enqueueNeighbors($queue, $queued, $cellXIndex, $cellYIndex, $cellZIndex, $cellYCount);
        }

        while (!$queue->isEmpty()) {
            $cellIndex  = $queue->dequeue();
            $cellXIndex = $cellIndex % self::CELL_X_COUNT;
            $cellZIndex = (int) ($cellIndex / self::CELL_X_COUNT) % self::CELL_Z_COUNT;
            $cellYIndex = (int) ($cellIndex / (self::CELL_X_COUNT * self::CELL_Z_COUNT));
            $cellX      = $cellXIndex * self::CELL_XZ_SIZE;
            $cellZ      = $cellZIndex * self::CELL_XZ_SIZE;
            $cellY      = $cellMinY + $cellYIndex * self::CELL_HEIGHT;

            if ($this->generateCell(
                $chunk,
                $multiMaterial,
                $functionContext,
                $chunkBaseX,
                $chunkBaseZ,
                $minY,
                $maxY,
                $cellX,
                $cellY,
                $cellZ
            )) {
                $this->enqueueNeighbors($queue, $queued, $cellXIndex, $cellYIndex, $cellZIndex, $cellYCount);
            }
        }

        $stoneId   = VanillaBlocks::STONE()->getStateId();
        $bedrockId = VanillaBlocks::BEDROCK()->getStateId();
        for ($x = 0; $x < 16; $x++) {
            for ($z = 0; $z < 16; $z++) {
                $chunk->setBlockStateId($x, $minY, $z, $bedrockId);
                $bedrockDepth = mt_rand(0, 5);
                for ($i = 0; $i < $bedrockDepth; $i++) {
                    $y = $minY + $i;
                    if ($chunk->getBlockStateId($x, $y, $z) !== VanillaBlocks::AIR()->getStateId()) {
                        $chunk->setBlockStateId($x, $y, $z, $bedrockId);
                    }
                }
            }
        }

        DensityCommon::releaseChunkCache($chunk);
    }

    private function generateCell(
        Chunk $chunk,
        MultiMaterial $multiMaterial,
        CellFunctionContext $functionContext,
        int $chunkBaseX,
        int $chunkBaseZ,
        int $minY,
        int $maxY,
        int $cellX,
        int $cellY,
        int $cellZ
    ): bool {
        $hasNonAir = false;
        $airId     = VanillaBlocks::AIR()->getStateId();
        $stoneId   = VanillaBlocks::STONE()->getStateId();

        for ($localX = 0; $localX < self::CELL_XZ_SIZE; $localX++) {
            $x      = $cellX + $localX;
            $worldX = $chunkBaseX + $x;

            for ($localZ = 0; $localZ < self::CELL_XZ_SIZE; $localZ++) {
                $z      = $cellZ + $localZ;
                $worldZ = $chunkBaseZ + $z;

                for ($localY = self::CELL_HEIGHT - 1; $localY >= 0; $localY--) {
                    $y = $cellY + $localY;
                    if ($y < $minY || $y > $maxY) {
                        continue;
                    }

                    $ctx   = $functionContext->set($worldX, $y, $worldZ);
                    $block = $multiMaterial->calculate($ctx);

                    if ($block !== null) {
                        $chunk->setBlockStateId($x, $y, $z, $block->getStateId());
                        if ($block->getStateId() !== $airId) {
                            $hasNonAir = true;
                            if ($y > $chunk->getHeightMap($x, $z)) {
                                $chunk->setHeightMap($x, $z, $y);
                            }
                        }
                    }
                }
            }
        }

        return $hasNonAir;
    }

    private function shouldGenerateMandatoryCell(array $mandatoryTopY, int $cellX, int $cellY, int $cellZ): bool
    {
        if ($cellY + self::CELL_HEIGHT - 1 <= self::SEA_LEVEL) {
            return true;
        }
        for ($localX = 0; $localX < self::CELL_XZ_SIZE; $localX++) {
            $x = $cellX + $localX;
            for ($localZ = 0; $localZ < self::CELL_XZ_SIZE; $localZ++) {
                $z = $cellZ + $localZ;
                if ($cellY <= $mandatoryTopY[$x][$z]) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isCornerFloodSeedCell(int $cellXIndex, int $cellYIndex, int $cellY, int $cellZIndex): bool
    {
        if ($cellY > self::CORNER_FLOOD_SEED_MAX_Y) {
            return false;
        }
        if ($cellYIndex % 3 !== 0) {
            return false;
        }
        return $this->isNorthWestCornerSeed($cellXIndex, $cellZIndex)
            || $this->isNorthEastCornerSeed($cellXIndex, $cellZIndex)
            || $this->isSouthWestCornerSeed($cellXIndex, $cellZIndex)
            || $this->isSouthEastCornerSeed($cellXIndex, $cellZIndex);
    }

    private function isNorthWestCornerSeed(int $x, int $z): bool
    {
        return ($x === 0 && $z === 0) || ($x === 1 && $z === 0) || ($x === 0 && $z === 1);
    }

    private function isNorthEastCornerSeed(int $x, int $z): bool
    {
        return ($x === self::CELL_X_COUNT - 1 && $z === 0)
            || ($x === self::CELL_X_COUNT - 2 && $z === 0)
            || ($x === self::CELL_X_COUNT - 1 && $z === 1);
    }

    private function isSouthWestCornerSeed(int $x, int $z): bool
    {
        return ($x === 0 && $z === self::CELL_Z_COUNT - 1)
            || ($x === 1 && $z === self::CELL_Z_COUNT - 1)
            || ($x === 0 && $z === self::CELL_Z_COUNT - 2);
    }

    private function isSouthEastCornerSeed(int $x, int $z): bool
    {
        return ($x === self::CELL_X_COUNT - 1 && $z === self::CELL_Z_COUNT - 1)
            || ($x === self::CELL_X_COUNT - 2 && $z === self::CELL_Z_COUNT - 1)
            || ($x === self::CELL_X_COUNT - 1 && $z === self::CELL_Z_COUNT - 2);
    }

    private function enqueueCell(
        \SplQueue $queue,
        array &$queued,
        int $cellXIndex,
        int $cellYIndex,
        int $cellZIndex,
        int $cellYCount
    ): void {
        if ($cellXIndex < 0 || $cellXIndex >= self::CELL_X_COUNT
            || $cellYIndex < 0 || $cellYIndex >= $cellYCount
            || $cellZIndex < 0 || $cellZIndex >= self::CELL_Z_COUNT
        ) {
            return;
        }
        $index = $this->cellIndex($cellXIndex, $cellYIndex, $cellZIndex);
        if ($queued[$index]) {
            return;
        }
        $queued[$index] = true;
        $queue->enqueue($index);
    }

    private function enqueueNeighbors(
        \SplQueue $queue,
        array &$queued,
        int $cellXIndex,
        int $cellYIndex,
        int $cellZIndex,
        int $cellYCount
    ): void {
        $this->enqueueCell($queue, $queued, $cellXIndex + 1, $cellYIndex,     $cellZIndex,     $cellYCount);
        $this->enqueueCell($queue, $queued, $cellXIndex - 1, $cellYIndex,     $cellZIndex,     $cellYCount);
        $this->enqueueCell($queue, $queued, $cellXIndex,     $cellYIndex + 1, $cellZIndex,     $cellYCount);
        $this->enqueueCell($queue, $queued, $cellXIndex,     $cellYIndex - 1, $cellZIndex,     $cellYCount);
        $this->enqueueCell($queue, $queued, $cellXIndex,     $cellYIndex,     $cellZIndex + 1, $cellYCount);
        $this->enqueueCell($queue, $queued, $cellXIndex,     $cellYIndex,     $cellZIndex - 1, $cellYCount);
    }

    private function cellIndex(int $cellXIndex, int $cellYIndex, int $cellZIndex): int
    {
        return ($cellYIndex * self::CELL_Z_COUNT + $cellZIndex) * self::CELL_X_COUNT + $cellXIndex;
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        // nothing
    }

    public function getSettings(): array
    {
        return [
            'preset' => $this->preset,
            'minY'   => $this->minY,
            'maxY'   => $this->maxY,
        ];
    }

    public function getWorldHeight(): int
    {
        return $this->maxY - $this->minY + 1;
    }

    public function beginAquifer(): void {
        
    }
}