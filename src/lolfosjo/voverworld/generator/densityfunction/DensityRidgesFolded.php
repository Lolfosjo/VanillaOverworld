<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

use lolfosjo\voverworld\generator\densityfunction\MappedType;

final class DensityRidgesFolded
{
    private function __construct() {}

    public static function overworldRidgesFolded(DensityFunction $ridges): DensityFunction
    {
        $absRidges = DensityCommon::map($ridges, MappedType::ABS);
        $inner = DensityCommon::add(
            DensityCommon::constant(-0.6666666666666666),
            $absRidges
        );
        $absInner = DensityCommon::map($inner, MappedType::ABS);
        $outer = DensityCommon::add(
            DensityCommon::constant(-0.3333333333333333),
            $absInner
        );
        return DensityCommon::mul(DensityCommon::constant(-3.0), $outer);
    }
}