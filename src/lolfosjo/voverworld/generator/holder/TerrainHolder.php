<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\generator\densityfunction\ChunkCache;
use lolfosjo\voverworld\generator\densityfunction\DensityCommon;
use lolfosjo\voverworld\generator\densityfunction\DensityFunction;
use lolfosjo\voverworld\generator\densityfunction\NormalNoiseAdapter;
use lolfosjo\voverworld\generator\densityfunction\DensityContinents;
use lolfosjo\voverworld\generator\densityfunction\DensityErosion;
use lolfosjo\voverworld\generator\densityfunction\DensityRidges;
use lolfosjo\voverworld\generator\densityfunction\DensityRidgesFolded;
use lolfosjo\voverworld\generator\densityfunction\DensityOffset;
use lolfosjo\voverworld\generator\densityfunction\DensityDepth;
use lolfosjo\voverworld\generator\densityfunction\DensityFactor;
use lolfosjo\voverworld\generator\densityfunction\DensityJaggedness;
use lolfosjo\voverworld\generator\densityfunction\DensityBase3dNoise;
use lolfosjo\voverworld\generator\densityfunction\DensitySlopedCheese;
use lolfosjo\voverworld\generator\densityfunction\OverworldCavesDensity;
use lolfosjo\voverworld\generator\densityfunction\FlatCacheMarker;
use lolfosjo\voverworld\generator\densityfunction\FunctionContext;
use lolfosjo\voverworld\generator\material\Aquifer;
use lolfosjo\voverworld\generator\material\MaterialFiller;
use lolfosjo\voverworld\generator\material\MultiMaterial;
use lolfosjo\voverworld\generator\noise\minecraft\noise\NormalNoise;
use lolfosjo\voverworld\util\math\RandomSourceProvider;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class TerrainHolder extends RandomizedObjectHolder
{
    private NormalNoise $surfaceNoise;
    private NormalNoise $jagged;
    private NormalNoise $barrierNoise;
    private NormalNoise $fluidLevelFloodednessNoise;
    private NormalNoise $fluidLevelSpreadNoise;
    private NormalNoise $lavaNoise;
    private NormalNoise $pillar;
    private NormalNoise $pillarRareness;
    private NormalNoise $pillarThickness;
    private NormalNoise $spaghetti2d;
    private NormalNoise $spaghetti2dElevation;
    private NormalNoise $spaghetti2dModulator;
    private NormalNoise $spaghetti2dThickness;
    private NormalNoise $spaghetti3dFirst;
    private NormalNoise $spaghetti3dSecond;
    private NormalNoise $spaghetti3dRarity;
    private NormalNoise $spaghetti3dThickness;
    private NormalNoise $spaghettiRoughness;
    private NormalNoise $spaghettiRoughnessModulator;
    private NormalNoise $caveEntrance;
    private NormalNoise $caveLayer;
    private NormalNoise $caveCheese;
    private NormalNoise $noodle;
    private NormalNoise $noodleThickness;
    private NormalNoise $noodleRidgeA;
    private NormalNoise $noodleRidgeB;
    private NormalNoise $veinToggleNoise;
    private NormalNoise $veinANoise;
    private NormalNoise $veinBNoise;
    private NormalNoise $oreGapNoise;

    private DensityFunction $continents;
    private DensityFunction $erosion;
    private DensityFunction $ridges;
    private DensityFunction $ridgesFolded;
    private DensityFunction $offset;
    private DensityFunction $depth;
    private DensityFunction $factor;
    private DensityFunction $jaggedness;
    private DensityFunction $base3d;
    private DensityFunction $slopedCheese;
    private DensityFunction $densityFunction;
    private DensityFunction $preliminarySurfaceDensity;
    private DensityFunction $preliminarySurfaceUpperBound;
    private DensityFunction $wrappedDensity;
    private FlatCacheMarker $flatCache;

    private MultiMaterial $multiMaterial;
    private ?Aquifer $aquifer = null;

    public function __construct(RandomSourceProvider $randomSourceProvider, BiomeHolder $biomeHolder)
    {
        parent::__construct($randomSourceProvider);

        $this->surfaceNoise              = new NormalNoise($randomSourceProvider->identical(), -6,  [1, 1, 1]);
        $this->jagged                    = new NormalNoise($randomSourceProvider->identical(), -16, [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]);

        $this->barrierNoise              = new NormalNoise($randomSourceProvider->fork(), -3,  [1, 1, 1]);
        $this->fluidLevelFloodednessNoise = new NormalNoise($randomSourceProvider->fork(), -7,  [1, 1, 0, 1]);
        $this->fluidLevelSpreadNoise     = new NormalNoise($randomSourceProvider->fork(), -5,  [1, 1, 1]);
        $this->lavaNoise                 = new NormalNoise($randomSourceProvider->fork(), -1,  [1, 1]);
        $this->pillar                    = new NormalNoise($randomSourceProvider->fork(), -7,  [1, 1]);
        $this->pillarRareness            = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->pillarThickness           = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->spaghetti2d               = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->spaghetti2dElevation      = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->spaghetti2dModulator      = new NormalNoise($randomSourceProvider->fork(), -11, [1]);
        $this->spaghetti2dThickness      = new NormalNoise($randomSourceProvider->fork(), -11, [1]);
        $this->spaghetti3dFirst          = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->spaghetti3dSecond         = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->spaghetti3dRarity         = new NormalNoise($randomSourceProvider->fork(), -11, [1]);
        $this->spaghetti3dThickness      = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->spaghettiRoughness        = new NormalNoise($randomSourceProvider->fork(), -5,  [1]);
        $this->spaghettiRoughnessModulator = new NormalNoise($randomSourceProvider->fork(), -8, [1]);
        $this->caveEntrance              = new NormalNoise($randomSourceProvider->fork(), -7,  [0.4, 0.5, 1]);
        $this->caveLayer                 = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->caveCheese                = new NormalNoise($randomSourceProvider->fork(), -8,  [0.5, 1, 2, 1, 2, 1, 0, 2, 0]);
        $this->noodle                    = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->noodleThickness           = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->noodleRidgeA              = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->noodleRidgeB              = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->veinToggleNoise           = new NormalNoise($randomSourceProvider->fork(), -8,  [1]);
        $this->veinANoise                = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->veinBNoise                = new NormalNoise($randomSourceProvider->fork(), -7,  [1]);
        $this->oreGapNoise               = new NormalNoise($randomSourceProvider->fork(), -5,  [1]);

        $shiftNoiseAdapter      = new NormalNoiseAdapter($biomeHolder->getOffsetNoise());
        $continentalNoiseAdapter = new NormalNoiseAdapter($biomeHolder->getContinentalNoise());
        $erosionNoiseAdapter    = new NormalNoiseAdapter($biomeHolder->getErosionNoise());
        $ridgeNoiseAdapter      = new NormalNoiseAdapter($biomeHolder->getWeirdnessNoise());
        $jaggedNoiseAdapter     = new NormalNoiseAdapter($biomeHolder->getJaggedNoise());

        $this->continents = DensityContinents::overworldContinents(
            $continentalNoiseAdapter,
            DensityCommon::shiftA($shiftNoiseAdapter),
            DensityCommon::shiftB($shiftNoiseAdapter)
        );
        $this->erosion = DensityErosion::overworldErosion(
            $erosionNoiseAdapter,
            DensityCommon::shiftA($shiftNoiseAdapter),
            DensityCommon::shiftB($shiftNoiseAdapter)
        );
        $this->ridges = DensityRidges::overworldRidges(
            $ridgeNoiseAdapter,
            DensityCommon::shiftA($shiftNoiseAdapter),
            DensityCommon::shiftB($shiftNoiseAdapter)
        );
        $this->ridgesFolded = DensityRidgesFolded::overworldRidgesFolded($this->ridges);

        $this->offset     = DensityOffset::overworldOffset($this->continents, $this->erosion, $this->ridgesFolded);
        $this->depth      = DensityDepth::overworldDepth($this->offset);
        $this->factor     = DensityFactor::overworldFactor($this->continents, $this->erosion, $this->ridges, $this->ridgesFolded);
        $this->jaggedness = DensityJaggedness::overworldJaggedness($this->continents, $this->erosion, $this->ridges, $this->ridgesFolded);
        $this->base3d     = DensityBase3dNoise::overworld($randomSourceProvider->identical());

        $this->slopedCheese = DensitySlopedCheese::overworldSlopedCheese(
            $this->depth,
            $this->jaggedness,
            $this->factor,
            $this->base3d,
            $jaggedNoiseAdapter
        );

        $this->densityFunction = OverworldCavesDensity::finalDensity(
            $this->slopedCheese,
            new NormalNoiseAdapter($this->spaghettiRoughness),
            new NormalNoiseAdapter($this->spaghettiRoughnessModulator),
            new NormalNoiseAdapter($this->spaghetti2dThickness),
            new NormalNoiseAdapter($this->spaghetti2dModulator),
            new NormalNoiseAdapter($this->spaghetti2d),
            new NormalNoiseAdapter($this->spaghetti2dElevation),
            new NormalNoiseAdapter($this->spaghetti3dRarity),
            new NormalNoiseAdapter($this->spaghetti3dThickness),
            new NormalNoiseAdapter($this->spaghetti3dFirst),
            new NormalNoiseAdapter($this->spaghetti3dSecond),
            new NormalNoiseAdapter($this->caveEntrance),
            new NormalNoiseAdapter($this->caveLayer),
            new NormalNoiseAdapter($this->caveCheese),
            new NormalNoiseAdapter($this->pillar),
            new NormalNoiseAdapter($this->pillarRareness),
            new NormalNoiseAdapter($this->pillarThickness),
            new NormalNoiseAdapter($this->noodle),
            new NormalNoiseAdapter($this->noodleThickness),
            new NormalNoiseAdapter($this->noodleRidgeA),
            new NormalNoiseAdapter($this->noodleRidgeB)
        );

        $this->preliminarySurfaceDensity     = OverworldCavesDensity::preliminarySurfaceLevel($this->offset, $this->factor);
        $this->preliminarySurfaceUpperBound  = OverworldCavesDensity::preliminarySurfaceLevelUpperBound($this->offset, $this->factor);

        $this->flatCache = new FlatCacheMarker($this->preliminarySurfaceDensity);
        $this->preliminarySurfaceDensity = $this->flatCache;

        $this->wrappedDensity = DensityCommon::cacheAllInCell($this->densityFunction);

        $stone = VanillaBlocks::STONE();

        $builder = [];

        $builder[] = new class($this) implements MaterialFiller {
            private TerrainHolder $holder;

            public function __construct(TerrainHolder $holder)
            {
                $this->holder = $holder;
            }

            public function calculate(FunctionContext $context): ?Block
            {
                $aquifer = $this->holder->getAquifer();
                if ($aquifer !== null) {
                    return $aquifer->computeSubstance($context, $this->holder->getWrappedDensity()->compute($context));
                }
                return null;
            }
        };

        $builder[] = new class($stone, $this) implements MaterialFiller {
            private Block $stone;
            private TerrainHolder $holder;

            public function __construct(Block $stone, TerrainHolder $holder)
            {
                $this->stone = $stone;
                $this->holder = $holder;
            }

            public function calculate(FunctionContext $context): ?Block
            {
                return $this->holder->getWrappedDensity()->compute($context) > 0.0 ? $this->stone : null;
            }
        };

        $this->multiMaterial = new MultiMaterial($builder);
    }

    public function getDensityFunction(): DensityFunction  { return $this->densityFunction; }
    public function getWrappedDensity(): DensityFunction   { return $this->wrappedDensity; }
    public function getMultiMaterial(): MultiMaterial      { return $this->multiMaterial; }
    public function getAquifer(): ?Aquifer                 { return $this->aquifer; }

    public function getContinents(): DensityFunction     { return $this->continents; }
    public function getErosion(): DensityFunction        { return $this->erosion; }
    public function getRidges(): DensityFunction          { return $this->ridges; }
    public function getRidgesFolded(): DensityFunction    { return $this->ridgesFolded; }
    public function getOffset(): DensityFunction          { return $this->offset; }
    public function getDepth(): DensityFunction           { return $this->depth; }
    public function getFactor(): DensityFunction          { return $this->factor; }
    public function getJaggedness(): DensityFunction      { return $this->jaggedness; }
    public function getFlatCacheMarker(): FlatCacheMarker { return $this->flatCache; }

    public function beginAquifer(
    int $chunkX,
    int $chunkZ,
    int $seed,
    ChunkCache $chunkCache,
    int $minY,
    int $yBlockSize,
    int $seaLevel
): void {
    $this->flatCache->invalidate();
    $this->aquifer = new Aquifer(
        $chunkX,
        $chunkZ,
        $seed,
        $chunkCache,
        DensityCommon::noise(new NormalNoiseAdapter($this->barrierNoise), 1.0, 0.5),
        DensityCommon::noise(new NormalNoiseAdapter($this->fluidLevelFloodednessNoise), 1.0, 0.67),
        DensityCommon::noise(new NormalNoiseAdapter($this->fluidLevelSpreadNoise), 1.0, 0.7142857142857143),
        DensityCommon::noise(new NormalNoiseAdapter($this->lavaNoise), 1.0, 1.0),
        $this->erosion,
        $this->depth,
        $this->preliminarySurfaceDensity,
        $this->preliminarySurfaceUpperBound,
        -64,                                     // preliminarySurfaceLowerBound
        8,                                       // preliminarySurfaceCellHeight
        $minY,
        $yBlockSize,
        Aquifer::overworldFluidPicker($seaLevel)
    );
}

    public function endAquifer(): void
    {
        $this->aquifer = null;
    }
}