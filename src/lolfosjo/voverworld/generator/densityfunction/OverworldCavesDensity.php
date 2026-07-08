<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class OverworldCavesDensity
{
    private static ?YCoordinate $Y = null;

    private function __construct() {}


    public static function spaghettiRoughnessFunction(
        NoiseHolder $spaghettiRoughness,
        NoiseHolder $spaghettiRoughnessModulator
    ): DensityFunction {
        $roughness = DensityCommon::noise($spaghettiRoughness, 1.0, 1.0);
        $modulator = DensityCommon::mappedNoise($spaghettiRoughnessModulator, 0.0, -0.1);
        return DensityCommon::cacheOnce(
            DensityCommon::mul(
                $modulator,
                DensityCommon::add(
                    DensityCommon::map($roughness, MappedType::ABS),
                    DensityCommon::constant(-0.4)
                )
            )
        );
    }

    public static function spaghetti2dThicknessModulator(NoiseHolder $spaghetti2dThickness): DensityFunction
    {
        return DensityCommon::cacheOnce(
            // original: mappedNoise(noise, xzScale=2.0, yScale=1.0, min=-0.6, max=-1.3)
            // our signature: mappedNoise(noise, min, max, xzScale, yScale)
            DensityCommon::mappedNoise($spaghetti2dThickness, -0.6, -1.3, 2.0, 1.0)
        );
    }

    public static function entrances(
        DensityFunction $spaghettiRoughnessFunction,
        NoiseHolder $spaghetti3dRarity,
        NoiseHolder $spaghetti3dThickness,
        NoiseHolder $spaghetti3dFirst,
        NoiseHolder $spaghetti3dSecond,
        NoiseHolder $caveEntrance
    ): DensityFunction {
        $rarity = DensityCommon::cacheOnce(DensityCommon::noise($spaghetti3dRarity, 2.0, 1.0));
        $thickness = DensityCommon::mappedNoise($spaghetti3dThickness, -0.065, -0.088);
        $first = DensityCommon::weirdScaledSampler(
            $rarity,
            $spaghetti3dFirst,
            new RarityValueMapper(RarityValueMapper::TYPE1)
        );
        $second = DensityCommon::weirdScaledSampler(
            $rarity,
            $spaghetti3dSecond,
            new RarityValueMapper(RarityValueMapper::TYPE1)
        );
        $spaghetti = new Clamp(
            DensityCommon::add(DensityCommon::max($first, $second), $thickness),
            -1.0,
            1.0
        );
        $entrance = DensityCommon::noise($caveEntrance, 0.75, 0.5);
        $entranceGradient = DensityCommon::add(
            DensityCommon::add($entrance, DensityCommon::constant(0.37)),
            DensityCommon::yClampedGradient(-10, 30, 0.3, 0.0)
        );
        return DensityCommon::cacheOnce(
            DensityCommon::min(
                $entranceGradient,
                DensityCommon::add($spaghettiRoughnessFunction, $spaghetti)
            )
        );
    }

    public static function pillars(
        NoiseHolder $pillar,
        NoiseHolder $pillarRareness,
        NoiseHolder $pillarThickness
    ): DensityFunction {
        $pillarNoise = DensityCommon::noise($pillar, 25.0, 0.3);
        $rarity = DensityCommon::mappedNoise($pillarRareness, 0.0, -2.0);
        $thickness = DensityCommon::mappedNoise($pillarThickness, 0.0, 1.1);
        $combined = DensityCommon::add(
            DensityCommon::mul($pillarNoise, DensityCommon::constant(2.0)),
            $rarity
        );
        return DensityCommon::cacheOnce(
            DensityCommon::mul($combined, DensityCommon::map($thickness, MappedType::CUBE))
        );
    }

    public static function spaghetti2d(
        DensityFunction $spaghetti2dThicknessModulator,
        NoiseHolder $spaghetti2dModulator,
        NoiseHolder $spaghetti2d,
        NoiseHolder $spaghetti2dElevation
    ): DensityFunction {
        $modulator = DensityCommon::noise($spaghetti2dModulator, 2.0, 1.0);
        $sampled = DensityCommon::weirdScaledSampler(
            $modulator,
            $spaghetti2d,
            new RarityValueMapper(RarityValueMapper::TYPE2)
        );
        // original: mappedNoise(elevation, xzScale=1.0, yScale=0.0, min=-8.0, max=8.0)
        $elevation = DensityCommon::mappedNoise($spaghetti2dElevation, -8.0, 8.0, 1.0, 0.0);
        $temp = DensityCommon::add($elevation, DensityCommon::yClampedGradient(-64, 320, 8.0, -40.0));
        $absTemp = DensityCommon::map($temp, MappedType::ABS);
        $elevationGradient = DensityCommon::map(
            DensityCommon::add($absTemp, $spaghetti2dThicknessModulator),
            MappedType::CUBE
        );
        $adjusted = DensityCommon::add(
            $sampled,
            DensityCommon::mul(DensityCommon::constant(0.083), $spaghetti2dThicknessModulator)
        );
        return new Clamp(
            DensityCommon::max($adjusted, $elevationGradient),
            -1.0,
            1.0
        );
    }

    public static function noodle(
        NoiseHolder $noodle,
        NoiseHolder $noodleThickness,
        NoiseHolder $noodleRidgeA,
        NoiseHolder $noodleRidgeB
    ): DensityFunction {
        $toggle = self::yLimitedInterpolatable(
            DensityCommon::noise($noodle, 1.0, 1.0),
            -60,
            320,
            -1.0
        );
        $thickness = self::yLimitedInterpolatable(
            // original: mappedNoise(noise, xzScale=1.0, yScale=1.0, min=-0.05, max=-0.1)
            DensityCommon::mappedNoise($noodleThickness, -0.05, -0.1, 1.0, 1.0),
            -60,
            320,
            0.0
        );
        $ridgeA = self::yLimitedInterpolatable(
            DensityCommon::noise($noodleRidgeA, 2.6666666666666665, 2.6666666666666665),
            -60,
            320,
            0.0
        );
        $ridgeB = self::yLimitedInterpolatable(
            DensityCommon::noise($noodleRidgeB, 2.6666666666666665, 2.6666666666666665),
            -60,
            320,
            0.0
        );
        $ridges = DensityCommon::mul(
            DensityCommon::constant(1.5),
            DensityCommon::max(
                DensityCommon::map($ridgeA, MappedType::ABS),
                DensityCommon::map($ridgeB, MappedType::ABS)
            )
        );
        return DensityCommon::rangeChoice(
            $toggle,
            -1000000.0,
            0.0,
            DensityCommon::constant(64.0),
            DensityCommon::add($thickness, $ridges)
        );
    }

    public static function finalDensity(
        DensityFunction $slopedCheese,
        NoiseHolder $spaghettiRoughness,
        NoiseHolder $spaghettiRoughnessModulator,
        NoiseHolder $spaghetti2dThickness,
        NoiseHolder $spaghetti2dModulator,
        NoiseHolder $spaghetti2d,
        NoiseHolder $spaghetti2dElevation,
        NoiseHolder $spaghetti3dRarity,
        NoiseHolder $spaghetti3dThickness,
        NoiseHolder $spaghetti3dFirst,
        NoiseHolder $spaghetti3dSecond,
        NoiseHolder $caveEntrance,
        NoiseHolder $caveLayer,
        NoiseHolder $caveCheese,
        NoiseHolder $pillar,
        NoiseHolder $pillarRareness,
        NoiseHolder $pillarThickness,
        NoiseHolder $noodle,
        NoiseHolder $noodleThickness,
        NoiseHolder $noodleRidgeA,
        NoiseHolder $noodleRidgeB
    ): DensityFunction {
        $spaghettiRoughnessFunction = self::spaghettiRoughnessFunction($spaghettiRoughness, $spaghettiRoughnessModulator);
        $spaghetti2dThicknessModulator = self::spaghetti2dThicknessModulator($spaghetti2dThickness);
        $spaghetti2dFunction = self::spaghetti2d(
            $spaghetti2dThicknessModulator,
            $spaghetti2dModulator,
            $spaghetti2d,
            $spaghetti2dElevation
        );
        $entrances = self::entrances(
            $spaghettiRoughnessFunction,
            $spaghetti3dRarity,
            $spaghetti3dThickness,
            $spaghetti3dFirst,
            $spaghetti3dSecond,
            $caveEntrance
        );
        $pillars = self::pillars($pillar, $pillarRareness, $pillarThickness);
        $underground = self::underground(
            $slopedCheese,
            $spaghetti2dFunction,
            $spaghettiRoughnessFunction,
            $entrances,
            $pillars,
            $caveLayer,
            $caveCheese
        );
        $caves = DensityCommon::rangeChoice(
            $slopedCheese,
            -1000000.0,
            1.5625,
            DensityCommon::min(
                $slopedCheese,
                DensityCommon::mul(DensityCommon::constant(5.0), $entrances)
            ),
            $underground
        );
        $postProcessed = DensityCommon::map(
            DensityCommon::mul(
                DensityCommon::constant(0.64),
                DensityCommon::interpolated(
                    DensityCommon::blendDensity(
                        DensityCommon::add(
                            DensityCommon::constant(0.1171875),
                            DensityCommon::mul(
                                DensityCommon::yClampedGradient(-64, -40, 0.0, 1.0),
                                DensityCommon::add(
                                    DensityCommon::constant(-0.1171875),
                                    DensityCommon::add(
                                        DensityCommon::constant(-0.078125),
                                        DensityCommon::mul(
                                            DensityCommon::yClampedGradient(240, 256, 1.0, 0.0),
                                            DensityCommon::add(
                                                DensityCommon::constant(0.078125),
                                                $caves
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            MappedType::SQUEEZE
        );
        return DensityCommon::min(
            $postProcessed,
            self::noodle($noodle, $noodleThickness, $noodleRidgeA, $noodleRidgeB)
        );
    }

    public static function preliminarySurfaceLevel(DensityFunction $offset, DensityFunction $factor): DensityFunction
    {
        $base = DensityCommon::add(
            DensityCommon::yClampedGradient(-64, 320, 1.5, -1.5),
            DensityCommon::cache2d($offset)
        );
        $scaled = DensityCommon::map(
            DensityCommon::mul($base, DensityCommon::cache2d($factor)),
            MappedType::QUARTER_NEGATIVE
        );
        $clamped = new Clamp(
            DensityCommon::add(
                DensityCommon::constant(-0.703125),
                DensityCommon::mul(DensityCommon::constant(4.0), $scaled)
            ),
            -64.0,
            64.0
        );
        return DensityCommon::add(
            DensityCommon::constant(-0.390625),
            DensityCommon::add(
                DensityCommon::constant(0.1171875),
                DensityCommon::mul(
                    DensityCommon::yClampedGradient(-64, -40, 0.0, 1.0),
                    DensityCommon::add(
                        DensityCommon::constant(-0.1171875),
                        DensityCommon::add(
                            DensityCommon::constant(-0.078125),
                            DensityCommon::mul(
                                DensityCommon::yClampedGradient(240, 256, 1.0, 0.0),
                                DensityCommon::add(
                                    DensityCommon::constant(0.078125),
                                    $clamped
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function preliminarySurfaceLevelUpperBound(DensityFunction $offset, DensityFunction $factor): DensityFunction
    {
        return new Clamp(
            DensityCommon::add(
                DensityCommon::constant(128.0),
                DensityCommon::mul(
                    DensityCommon::constant(-128.0),
                    DensityCommon::add(
                        DensityCommon::mul(
                            DensityCommon::constant(0.2734375),
                            DensityCommon::map(DensityCommon::cache2d($factor), MappedType::INVERT)
                        ),
                        DensityCommon::mul(
                            DensityCommon::constant(-1.0),
                            DensityCommon::cache2d($offset)
                        )
                    )
                )
            ),
            -40.0,
            320.0
        );
    }

    private static function yLimitedInterpolatable(
        DensityFunction $density,
        int $minY,
        int $maxY,
        float $whenOutOfRange
    ): DensityFunction {
        if (self::$Y === null) {
            self::$Y = new YCoordinate();
        }
        return DensityCommon::interpolated(
            DensityCommon::rangeChoice(
                self::$Y,
                (float) $minY,
                (float) ($maxY + 1),
                $density,
                DensityCommon::constant($whenOutOfRange)
            )
        );
    }

    private static function underground(
        DensityFunction $slopedCheese,
        DensityFunction $spaghetti2d,
        DensityFunction $spaghettiRoughnessFunction,
        DensityFunction $entrances,
        DensityFunction $pillars,
        NoiseHolder $caveLayer,
        NoiseHolder $caveCheese
    ): DensityFunction {
        $layerizedCaverns = DensityCommon::mul(
            DensityCommon::constant(4.0),
            DensityCommon::map(
                DensityCommon::noise($caveLayer, 1.0, 8.0),
                MappedType::SQUARE
            )
        );
        $caveCheeseFunction = DensityCommon::add(
            new Clamp(
                DensityCommon::add(
                    DensityCommon::constant(0.27),
                    DensityCommon::noise($caveCheese, 1.0, 0.6666666666666666)
                ),
                -1.0,
                1.0
            ),
            new Clamp(
                DensityCommon::add(
                    DensityCommon::constant(1.5),
                    DensityCommon::mul(DensityCommon::constant(-0.64), $slopedCheese)
                ),
                0.0,
                0.5
            )
        );
        $caveDensity = DensityCommon::add($layerizedCaverns, $caveCheeseFunction);
        $passages = DensityCommon::min(
            DensityCommon::min($caveDensity, $entrances),
            DensityCommon::add($spaghetti2d, $spaghettiRoughnessFunction)
        );
        $pillarFilter = DensityCommon::rangeChoice(
            $pillars,
            -1000000.0,
            0.03,
            DensityCommon::constant(-1000000.0),
            $pillars
        );
        return DensityCommon::max($passages, $pillarFilter);
    }
}