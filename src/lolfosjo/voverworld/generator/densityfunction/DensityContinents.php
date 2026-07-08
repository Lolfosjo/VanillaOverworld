<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityContinents
{
    private const XZ_SCALE = 0.25;
    private const Y_SCALE = 0.0;

    private function __construct() {}

    public static function overworldContinents(
        NoiseHolder $continentalness,
        DensityFunction $shiftX,
        DensityFunction $shiftZ
    ): DensityFunction {
        return DensityCommon::flatCache(
            new ShiftedNoise(
                $shiftX,
                DensityCommon::zero(),
                $shiftZ,
                self::XZ_SCALE,
                self::Y_SCALE,
                $continentalness
            )
        );
    }

    public static function overworldContinentsWithShiftNoise(
        NoiseHolder $continentalness,
        NoiseHolder $shiftNoise
    ): DensityFunction {
        return self::overworldContinents(
            $continentalness,
            DensityCommon::shiftA($shiftNoise),
            DensityCommon::shiftB($shiftNoise)
        );
    }
}