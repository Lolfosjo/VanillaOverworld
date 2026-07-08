<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityFactor
{
    private function __construct() {}

    public static function overworldFactor(
        DensityFunction $continents,
        DensityFunction $erosion,
        DensityFunction $ridges,
        DensityFunction $ridgesFolded
    ): DensityFunction {
        return DensityCommon::flatCache(
            DensityCommon::cache2d(
                DensityCommon::add(
                    DensityCommon::constant(10.0),
                    DensityCommon::mul(
                        DensityCommon::blendAlpha(),
                        DensityCommon::add(
                            DensityCommon::constant(-10.0),
                            self::factorSpline($continents, $erosion, $ridges, $ridgesFolded)
                        )
                    )
                )
            )
        );
    }

    private static function factorSpline(
        DensityFunction $continents,
        DensityFunction $erosion,
        DensityFunction $ridges,
        DensityFunction $ridgesFolded
    ): DensityFunction {
        $ridgesA = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.2, 6.3, 0.0),
            DensityCommon::splinePoint(0.2, 6.25, 0.0)
        );
        $ridgesB = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.05, 6.3, 0.0),
            DensityCommon::splinePoint(0.05, 2.67, 0.0)
        );
        $ridgesC = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.05, 2.67, 0.0),
            DensityCommon::splinePoint(0.05, 6.3, 0.0)
        );
        $ridgesD = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.2, 6.3, 0.0),
            DensityCommon::splinePoint(0.2, 5.47, 0.0)
        );
        $ridgesE = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.2, 6.3, 0.0),
            DensityCommon::splinePoint(0.2, 5.08, 0.0)
        );
        $ridgesF = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(-0.2, 6.3, 0.0),
            DensityCommon::splinePoint(0.2, 4.69, 0.0)
        );
        $ridgesG = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(0.0, 6.25, 0.0),
            DensityCommon::splinePoint(0.1, 0.625, 0.0)
        );
        $ridgesH = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(0.0, 5.47, 0.0),
            DensityCommon::splinePoint(0.1, 0.625, 0.0)
        );
        $ridgesI = DensityCommon::splineFromPoints($ridges,
            DensityCommon::splinePoint(0.0, 5.08, 0.0),
            DensityCommon::splinePoint(0.1, 0.625, 0.0)
        );

        $ridgesFoldedA = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-0.9, 6.25, 0.0),
            DensityCommon::splinePoint(-0.69, $ridgesG, 0.0)
        );
        $ridgesFoldedB = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-0.9, 5.47, 0.0),
            DensityCommon::splinePoint(-0.69, $ridgesH, 0.0)
        );
        $ridgesFoldedC = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-0.9, 5.08, 0.0),
            DensityCommon::splinePoint(-0.69, $ridgesI, 0.0)
        );
        $ridgesFoldedD = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(0.45, $ridgesF, 0.0),
            DensityCommon::splinePoint(0.7, 1.56, 0.0)
        );
        $ridgesFoldedE = DensityCommon::splineFromPoints($ridgesFolded,
            DensityCommon::splinePoint(-0.7, $ridgesF, 0.0),
            DensityCommon::splinePoint(-0.15, 1.37, 0.0)
        );

        $erosion1 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.6, $ridgesA, 0.0),
            DensityCommon::splinePoint(-0.5, $ridgesB, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesA, 0.0),
            DensityCommon::splinePoint(-0.25, $ridgesA, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesC, 0.0),
            DensityCommon::splinePoint(0.03, $ridgesA, 0.0),
            DensityCommon::splinePoint(0.35, 6.25, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFoldedA, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFoldedA, 0.0),
            DensityCommon::splinePoint(0.62, 6.25, 0.0)
        );
        $erosion2 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.6, $ridgesD, 0.0),
            DensityCommon::splinePoint(-0.5, $ridgesB, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesD, 0.0),
            DensityCommon::splinePoint(-0.25, $ridgesD, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesC, 0.0),
            DensityCommon::splinePoint(0.03, $ridgesD, 0.0),
            DensityCommon::splinePoint(0.35, 5.47, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFoldedB, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFoldedB, 0.0),
            DensityCommon::splinePoint(0.62, 5.47, 0.0)
        );
        $erosion3 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.6, $ridgesE, 0.0),
            DensityCommon::splinePoint(-0.5, $ridgesB, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesE, 0.0),
            DensityCommon::splinePoint(-0.25, $ridgesE, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesC, 0.0),
            DensityCommon::splinePoint(0.03, $ridgesE, 0.0),
            DensityCommon::splinePoint(0.35, 5.08, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFoldedC, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFoldedC, 0.0),
            DensityCommon::splinePoint(0.62, 5.08, 0.0)
        );
        $erosion4 = DensityCommon::splineFromPoints($erosion,
            DensityCommon::splinePoint(-0.6, $ridgesF, 0.0),
            DensityCommon::splinePoint(-0.5, $ridgesB, 0.0),
            DensityCommon::splinePoint(-0.35, $ridgesF, 0.0),
            DensityCommon::splinePoint(-0.25, $ridgesF, 0.0),
            DensityCommon::splinePoint(-0.1, $ridgesC, 0.0),
            DensityCommon::splinePoint(0.03, $ridgesF, 0.0),
            DensityCommon::splinePoint(0.05, $ridgesFoldedD, 0.0),
            DensityCommon::splinePoint(0.4, $ridgesFoldedD, 0.0),
            DensityCommon::splinePoint(0.45, $ridgesFoldedE, 0.0),
            DensityCommon::splinePoint(0.55, $ridgesFoldedE, 0.0),
            DensityCommon::splinePoint(0.58, 4.69, 0.0)
        );

        return DensityCommon::splineFromPoints($continents,
            DensityCommon::splinePoint(-0.19, 3.95, 0.0),
            DensityCommon::splinePoint(-0.15, $erosion1, 0.0),
            DensityCommon::splinePoint(-0.1, $erosion2, 0.0),
            DensityCommon::splinePoint(0.03, $erosion3, 0.0),
            DensityCommon::splinePoint(0.06, $erosion4, 0.0)
        );
    }
}