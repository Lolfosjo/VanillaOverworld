<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityRidges
{
    private const XZ_SCALE = 0.25;
    private const Y_SCALE = 0.0;

    private function __construct() {}

    public static function overworldRidges(
        NoiseHolder $ridge,
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
                $ridge
            )
        );
    }

    public static function overworldRidgesWithShiftNoise(
        NoiseHolder $ridge,
        NoiseHolder $shiftNoise
    ): DensityFunction {
        return self::overworldRidges(
            $ridge,
            DensityCommon::shiftA($shiftNoise),
            DensityCommon::shiftB($shiftNoise)
        );
    }
}