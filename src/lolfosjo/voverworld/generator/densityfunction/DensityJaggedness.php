<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityJaggedness
{
    private function __construct() {}

    public static function overworldJaggedness(
        DensityFunction $continents,
        DensityFunction $erosion,
        DensityFunction $ridges,
        DensityFunction $ridgesFolded
    ): DensityFunction {
        return DensityCommon::flatCache(
            DensityCommon::cache2d(
                DensityCommon::add(
                    DensityCommon::constant(0.0),
                    DensityCommon::mul(
                        DensityCommon::blendAlpha(),
                        DensityCommon::add(
                            DensityCommon::constant(-0.0),
                            self::jaggednessSpline($continents, $erosion, $ridges, $ridgesFolded)
                        )
                    )
                )
            )
        );
    }

    private static function jaggednessSpline(
        DensityFunction $continents,
        DensityFunction $erosion,
        DensityFunction $ridges,
        DensityFunction $ridgesFolded
    ): DensityFunction {
        $ridges1 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.63, 0.0),
            DensityCommon::splinePoint(0.01, 0.3, 0.0)
        );
        $ridges2 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.315, 0.0),
            DensityCommon::splinePoint(0.01, 0.15, 0.0)
        );
        $ridges3 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.315, 0.0),
            DensityCommon::splinePoint(0.01, 0.15, 0.0)
        );
        $ridges4 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.63, 0.0),
            DensityCommon::splinePoint(0.01, 0.3, 0.0)
        );
        $ridges5 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.63, 0.0),
            DensityCommon::splinePoint(0.01, 0.3, 0.0)
        );
        $ridges6 = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.01, 0.63, 0.0),
            DensityCommon::splinePoint(0.01, 0.3, 0.0)
        );

        $ridgesFolded1 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.19999999, 0.0, 0.0),
            DensityCommon::splinePoint(0.44999996, 0.0, 0.0),
            DensityCommon::splinePoint(1.0, $ridges1, 0.0)
        );
        $ridgesFolded2 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.19999999, 0.0, 0.0),
            DensityCommon::splinePoint(0.44999996, 0.0, 0.0),
            DensityCommon::splinePoint(1.0, $ridges2, 0.0)
        );
        $ridgesFolded3 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.19999999, 0.0, 0.0),
            DensityCommon::splinePoint(0.44999996, 0.0, 0.0),
            DensityCommon::splinePoint(1.0, $ridges3, 0.0)
        );
        $ridgesFolded4 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.19999999, 0.0, 0.0),
            DensityCommon::splinePoint(0.44999996, $ridges4, 0.0),
            DensityCommon::splinePoint(1.0, $ridges5, 0.0)
        );
        $ridgesFolded5 = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.19999999, 0.0, 0.0),
            DensityCommon::splinePoint(0.44999996, 0.0, 0.0),
            DensityCommon::splinePoint(1.0, $ridges6, 0.0)
        );

        $erosion1 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-1.0, $ridgesFolded1, 0.0),
            DensityCommon::splinePoint(-0.78, $ridgesFolded2, 0.0),
            DensityCommon::splinePoint(-0.5775, $ridgesFolded3, 0.0),
            DensityCommon::splinePoint(-0.375, 0.0, 0.0)
        );
        $erosion2 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-1.0, $ridgesFolded4, 0.0),
            DensityCommon::splinePoint(-0.78, $ridgesFolded5, 0.0),
            DensityCommon::splinePoint(-0.5775, $ridgesFolded5, 0.0),
            DensityCommon::splinePoint(-0.375, 0.0, 0.0)
        );

        return DensityCommon::splineFromPoints($continents,
            DensityCommon::splinePoint(-0.11, 0.0, 0.0),
            DensityCommon::splinePoint(0.03, $erosion1, 0.0),
            DensityCommon::splinePoint(0.65, $erosion2, 0.0)
        );
    }
}