<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityOffset
{
    private const BASE_OFFSET = -0.5037500262260437;

    private function __construct() {}

    public static function overworldOffset(
        DensityFunction $continents,
        DensityFunction $erosion,
        DensityFunction $ridgesFolded
    ): DensityFunction {
        $blendAlpha = DensityCommon::cacheOnce(DensityCommon::blendAlpha());
        $blendTerm = DensityCommon::mul(
            DensityCommon::blendOffset(),
            DensityCommon::add(
                DensityCommon::constant(1.0),
                DensityCommon::mul(DensityCommon::constant(-1.0), $blendAlpha)
            )
        );

        $offsetTerm = DensityCommon::mul(
            DensityCommon::add(DensityCommon::constant(self::BASE_OFFSET), self::offsetSpline($continents, $erosion, $ridgesFolded)),
            $blendAlpha
        );

        return DensityCommon::flatCache(
            DensityCommon::cache2d(
                DensityCommon::add($blendTerm, $offsetTerm)
            )
        );
    }

    private static function offsetSpline(DensityFunction $continents, DensityFunction $erosion, DensityFunction $ridgesFolded): DensityFunction
    {
        $ridgesFolded1 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.08880186, 0.38940096),
            DensityCommon::splinePoint(1.0, 0.69000006, 0.38940096)
        );
        $ridgesFolded2 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.115760356, 0.37788022),
            DensityCommon::splinePoint(1.0, 0.6400001, 0.37788022)
        );
        $ridgesFolded3 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.2222, 0.0),
            DensityCommon::splinePoint(-0.75, -0.2222, 0.0),
            DensityCommon::splinePoint(-0.65, 0.0, 0.0),
            DensityCommon::splinePoint(0.5954547, 2.9802322E-8, 0.0),
            DensityCommon::splinePoint(0.6054547, 2.9802322E-8, 0.2534563),
            DensityCommon::splinePoint(1.0, 0.100000024, 0.2534563)
        );
        $ridgesFolded4 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.3, 0.5),
            DensityCommon::splinePoint(-0.4, 0.05, 0.0),
            DensityCommon::splinePoint(0.0, 0.05, 0.0),
            DensityCommon::splinePoint(0.4, 0.05, 0.0),
            DensityCommon::splinePoint(1.0, 0.060000002, 0.007000001)
        );
        $ridgesFolded5 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.15, 0.5),
            DensityCommon::splinePoint(-0.4, 0.0, 0.0),
            DensityCommon::splinePoint(0.0, 0.0, 0.0),
            DensityCommon::splinePoint(0.4, 0.05, 0.1),
            DensityCommon::splinePoint(1.0, 0.060000002, 0.007000001)
        );
        $ridgesFolded6 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.15, 0.5),
            DensityCommon::splinePoint(-0.4, 0.0, 0.0),
            DensityCommon::splinePoint(0.0, 0.0, 0.0),
            DensityCommon::splinePoint(0.4, 0.0, 0.0),
            DensityCommon::splinePoint(1.0, 0.0, 0.0)
        );
        $ridgesFolded7 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.02, 0.0),
            DensityCommon::splinePoint(-0.4, -0.03, 0.0),
            DensityCommon::splinePoint(0.0, -0.03, 0.0),
            DensityCommon::splinePoint(0.4, 0.0, 0.06),
            DensityCommon::splinePoint(1.0, 0.0, 0.0)
        );
        $ridgesFolded8 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.25, 0.5),
            DensityCommon::splinePoint(-0.4, 0.05, 0.0),
            DensityCommon::splinePoint(0.0, 0.05, 0.0),
            DensityCommon::splinePoint(0.4, 0.05, 0.0),
            DensityCommon::splinePoint(1.0, 0.060000002, 0.007000001)
        );
        $ridgesFolded9 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.1, 0.5),
            DensityCommon::splinePoint(-0.4, 0.001, 0.01),
            DensityCommon::splinePoint(0.0, 0.003, 0.01),
            DensityCommon::splinePoint(0.4, 0.05, 0.094000004),
            DensityCommon::splinePoint(1.0, 0.060000002, 0.007000001)
        );
        $ridgesFolded10 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.1, 0.5),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded11 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.02, 0.015),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded12 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, 0.20235021, 0.0),
            DensityCommon::splinePoint(0.0, 0.7161751, 0.5138249),
            DensityCommon::splinePoint(1.0, 1.23, 0.5138249)
        );
        $ridgesFolded13 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, 0.2, 0.0),
            DensityCommon::splinePoint(0.0, 0.44682026, 0.43317974),
            DensityCommon::splinePoint(1.0, 0.88, 0.43317974)
        );
        $ridgesFolded14 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, 0.2, 0.0),
            DensityCommon::splinePoint(0.0, 0.30829495, 0.3917051),
            DensityCommon::splinePoint(1.0, 0.70000005, 0.3917051)
        );
        $ridgesFolded15 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.25, 0.5),
            DensityCommon::splinePoint(-0.4, 0.35, 0.0),
            DensityCommon::splinePoint(0.0, 0.35, 0.0),
            DensityCommon::splinePoint(0.4, 0.35, 0.0),
            DensityCommon::splinePoint(1.0, 0.42000002, 0.049000014)
        );
        $ridgesFolded16 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.1, 0.5),
            DensityCommon::splinePoint(-0.4, 0.0069999998, 0.07),
            DensityCommon::splinePoint(0.0, 0.021, 0.07),
            DensityCommon::splinePoint(0.4, 0.35, 0.658),
            DensityCommon::splinePoint(1.0, 0.42000002, 0.049000014)
        );
        $ridgesFolded17 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.1, 0.5),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded18 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.05, 0.5),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded19 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, 0.2, 0.0),
            DensityCommon::splinePoint(0.0, 0.5391705, 0.4608295),
            DensityCommon::splinePoint(1.0, 1.0, 0.4608295)
        );
        $ridgesFolded20 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.2, 0.5),
            DensityCommon::splinePoint(-0.4, 0.5, 0.0),
            DensityCommon::splinePoint(0.0, 0.5, 0.0),
            DensityCommon::splinePoint(0.4, 0.5, 0.0),
            DensityCommon::splinePoint(1.0, 0.6, 0.070000015)
        );
        $ridgesFolded21 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.05, 0.5),
            DensityCommon::splinePoint(-0.4, 0.01, 0.099999994),
            DensityCommon::splinePoint(0.0, 0.03, 0.099999994),
            DensityCommon::splinePoint(0.4, 0.5, 0.94),
            DensityCommon::splinePoint(1.0, 0.6, 0.070000015)
        );
        $ridgesFolded22 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.05, 0.5),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded23 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.02, 0.015),
            DensityCommon::splinePoint(-0.4, 0.01, 0.0),
            DensityCommon::splinePoint(0.0, 0.01, 0.0),
            DensityCommon::splinePoint(0.4, 0.03, 0.04),
            DensityCommon::splinePoint(1.0, 0.1, 0.049)
        );
        $ridgesFolded24 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, 0.34792626, 0.0),
            DensityCommon::splinePoint(0.0, 0.9239631, 0.5760369),
            DensityCommon::splinePoint(1.0, 1.5, 0.5760369)
        );
        $ridgesFolded25 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.1, 0.0),
            DensityCommon::splinePoint(-0.4, 0.1, 0.0),
            DensityCommon::splinePoint(0.0, 0.17, 0.0)
        );
        $ridgesFolded26 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-1.0, -0.05, 0.0),
            DensityCommon::splinePoint(-0.4, 0.1, 0.0),
            DensityCommon::splinePoint(0.0, 0.17, 0.0)
        );

        $erosion1 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.85, $ridgesFolded1, 0.0),
            DensityCommon::splinePoint(-0.7, $ridgesFolded2, 0.0),
            DensityCommon::splinePoint(-0.4, $ridgesFolded3, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesFolded4, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesFolded5, 0.0),
            DensityCommon::splinePoint(0.2, $ridgesFolded6, 0.0),
            DensityCommon::splinePoint(0.7, $ridgesFolded7, 0.0)
        );
        $erosion2 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.85, $ridgesFolded1, 0.0),
            DensityCommon::splinePoint(-0.7, $ridgesFolded2, 0.0),
            DensityCommon::splinePoint(-0.4, $ridgesFolded3, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesFolded8, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesFolded9, 0.0),
            DensityCommon::splinePoint(0.2, $ridgesFolded10, 0.0),
            DensityCommon::splinePoint(0.7, $ridgesFolded11, 0.0)
        );
        $erosion3 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.85, $ridgesFolded12, 0.0),
            DensityCommon::splinePoint(-0.7, $ridgesFolded13, 0.0),
            DensityCommon::splinePoint(-0.4, $ridgesFolded14, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesFolded15, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesFolded16, 0.0),
            DensityCommon::splinePoint(0.2, $ridgesFolded17, 0.0),
            DensityCommon::splinePoint(0.4, $ridgesFolded17, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFolded25, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFolded25, 0.0),
            DensityCommon::splinePoint(0.58, $ridgesFolded18, 0.0),
            DensityCommon::splinePoint(0.7, $ridgesFolded11, 0.0)
        );
        $erosion4 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.85, $ridgesFolded24, 0.0),
            DensityCommon::splinePoint(-0.7, $ridgesFolded19, 0.0),
            DensityCommon::splinePoint(-0.4, $ridgesFolded19, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesFolded20, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesFolded21, 0.0),
            DensityCommon::splinePoint(0.2, $ridgesFolded22, 0.0),
            DensityCommon::splinePoint(0.4, $ridgesFolded22, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFolded26, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFolded26, 0.0),
            DensityCommon::splinePoint(0.58, $ridgesFolded22, 0.0),
            DensityCommon::splinePoint(0.7, $ridgesFolded23, 0.0)
        );

        return DensityCommon::splineFromPoints($continents,
            DensityCommon::splinePoint(-1.1, 0.044, 0.0),
            DensityCommon::splinePoint(-1.02, -0.2222, 0.0),
            DensityCommon::splinePoint(-0.51, -0.2222, 0.0),
            DensityCommon::splinePoint(-0.44, -0.12, 0.0),
            DensityCommon::splinePoint(-0.18, -0.12, 0.0),
            DensityCommon::splinePoint(-0.16, $erosion1, 0.0),
            DensityCommon::splinePoint(-0.15, $erosion1, 0.0),
            DensityCommon::splinePoint(-0.1, $erosion2, 0.0),
            DensityCommon::splinePoint(0.25, $erosion3, 0.0),
            DensityCommon::splinePoint(1.0, $erosion4, 0.0)
        );
    }
}