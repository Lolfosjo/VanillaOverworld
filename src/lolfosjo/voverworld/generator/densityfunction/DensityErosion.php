<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityErosion
{
    private const XZ_SCALE = 0.25;
    private const Y_SCALE = 0.0;

    private function __construct() {}

    public static function overworldErosion(
        NoiseHolder $erosion,
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
                $erosion
            )
        );
    }

    public static function overworldErosionWithShiftNoise(
        NoiseHolder $erosion,
        NoiseHolder $shiftNoise
    ): DensityFunction {
        return self::overworldErosion(
            $erosion,
            DensityCommon::shiftA($shiftNoise),
            DensityCommon::shiftB($shiftNoise)
        );
    }
}