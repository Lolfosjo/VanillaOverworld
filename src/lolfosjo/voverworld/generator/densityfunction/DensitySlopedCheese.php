<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensitySlopedCheese
{
    private const JAGGED_XZ_SCALE = 1500.0;
    private const JAGGED_Y_SCALE = 0.0;

    private function __construct() {}

    public static function overworldSlopedCheese(
        DensityFunction $depth,
        DensityFunction $jaggedness,
        DensityFunction $factor,
        DensityFunction $base3dNoise,
        NoiseHolder $jaggedNoise
    ): DensityFunction {
        $jaggedSample = DensityCommon::map(
            DensityCommon::noise($jaggedNoise, self::JAGGED_XZ_SCALE, self::JAGGED_Y_SCALE),
            MappedType::HALF_NEGATIVE
        );

        $combinedDepth = DensityCommon::add(
            $depth,
            DensityCommon::mul($jaggedness, $jaggedSample)
        );

        $quarterNeg = DensityCommon::map(
            DensityCommon::mul($combinedDepth, $factor),
            MappedType::QUARTER_NEGATIVE
        );

        return DensityCommon::add(
            DensityCommon::mul(DensityCommon::constant(4.0), $quarterNeg),
            $base3dNoise
        );
    }
}