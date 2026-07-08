<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class FactorSpline
{
    private static ?Spline $cachedSpline = null;

    public static function getCachedSpline(): Spline
    {
        if (self::$cachedSpline !== null) {
            return self::$cachedSpline;
        }

        // Ridges Splines 
        $ridges1 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(6.25), 0.0),
        ]);

        $ridges2 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.05, new StaticValue(6.3), 0.0),
            new Point(0.05, new StaticValue(2.67), 0.0),
        ]);

        $ridges3 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(6.25), 0.0),
        ]);

        $ridges4 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(6.25), 0.0),
        ]);

        $ridges5 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.05, new StaticValue(2.67), 0.0),
            new Point(0.05, new StaticValue(6.3), 0.0),
        ]);

        $ridges6 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(6.25), 0.0),
        ]);

        $ridges7 = new Spline("minecraft:overworld/ridges", [
            new Point(0.0, new StaticValue(6.25), 0.0),
            new Point(0.1, new StaticValue(0.625), 0.0),
        ]);

        $ridges8 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(5.47), 0.0),
        ]);

        $ridges9 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(5.08), 0.0),
        ]);

        $ridges10 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.2, new StaticValue(6.3), 0.0),
            new Point(0.2, new StaticValue(4.69), 0.0),
        ]);

        $ridges11 = new Spline("minecraft:overworld/ridges", [
            new Point(0.0, new StaticValue(5.47), 0.0),
            new Point(0.1, new StaticValue(0.625), 0.0),
        ]);

        $ridges12 = new Spline("minecraft:overworld/ridges", [
            new Point(0.0, new StaticValue(5.08), 0.0),
            new Point(0.1, new StaticValue(0.625), 0.0),
        ]);

        $ridges13 = new Spline("minecraft:overworld/ridges", [
            new Point(0.0, new StaticValue(4.69), 0.0),
            new Point(0.1, new StaticValue(0.625), 0.0),
        ]);

        // Ridges Folded Splines 
        $ridgesFolded1 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-0.9, new StaticValue(6.25), 0.0),
            new Point(-0.69, $ridges7, 0.0),
        ]);

        $ridgesFolded2 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-0.9, new StaticValue(5.47), 0.0),
            new Point(-0.69, $ridges11, 0.0),
        ]);

        $ridgesFolded3 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-0.9, new StaticValue(5.08), 0.0),
            new Point(-0.69, $ridges12, 0.0),
        ]);

        $ridgesFolded4 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.45, $ridges10, 0.0),
            new Point(0.7, new StaticValue(1.56), 0.0),
        ]);

        $ridgesFolded5 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-0.7, $ridges10, 0.0),
            new Point(-0.15, new StaticValue(1.37), 0.0),
        ]);

        // Erosion Splines 
        $erosion1 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.6, $ridges1, 0.0),
            new Point(-0.5, $ridges2, 0.0),
            new Point(-0.35, $ridges3, 0.0),
            new Point(-0.25, $ridges4, 0.0),
            new Point(-0.1, $ridges5, 0.0),
            new Point(0.03, $ridges6, 0.0),
            new Point(0.35, new StaticValue(6.25), 0.0),
            new Point(0.45, $ridgesFolded1, 0.0),
            new Point(0.55, $ridgesFolded1, 0.0),
            new Point(0.62, new StaticValue(6.25), 0.0),
        ]);

        $erosion2 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.6, $ridges8, 0.0),
            new Point(-0.5, $ridges2, 0.0),
            new Point(-0.35, $ridges8, 0.0),
            new Point(-0.25, $ridges8, 0.0),
            new Point(-0.1, $ridges5, 0.0),
            new Point(0.03, $ridges8, 0.0),
            new Point(0.35, new StaticValue(5.47), 0.0),
            new Point(0.45, $ridgesFolded2, 0.0),
            new Point(0.55, $ridgesFolded2, 0.0),
            new Point(0.62, new StaticValue(5.47), 0.0),
        ]);

        $erosion3 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.6, $ridges9, 0.0),
            new Point(-0.5, $ridges2, 0.0),
            new Point(-0.35, $ridges9, 0.0),
            new Point(-0.25, $ridges9, 0.0),
            new Point(-0.1, $ridges5, 0.0),
            new Point(0.03, $ridges9, 0.0),
            new Point(0.35, new StaticValue(5.08), 0.0),
            new Point(0.45, $ridgesFolded3, 0.0),
            new Point(0.55, $ridgesFolded3, 0.0),
            new Point(0.62, new StaticValue(5.08), 0.0),
        ]);

        $erosion4 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.6, $ridges10, 0.0),
            new Point(-0.5, $ridges2, 0.0),
            new Point(-0.35, $ridges10, 0.0),
            new Point(-0.25, $ridges10, 0.0),
            new Point(-0.1, $ridges5, 0.0),
            new Point(0.03, $ridges10, 0.0),
            new Point(0.05, $ridgesFolded4, 0.0),
            new Point(0.4, $ridgesFolded4, 0.0),
            new Point(0.45, $ridgesFolded5, 0.0),
            new Point(0.55, $ridgesFolded5, 0.0),
            new Point(0.58, new StaticValue(4.69), 0.0),
        ]);

        // Final Cached Spline
        self::$cachedSpline = new Spline("minecraft:overworld/continents", [
            new Point(-0.19, new StaticValue(3.95), 0.0),
            new Point(-0.15, $erosion1, 0.0),
            new Point(-0.1, $erosion2, 0.0),
            new Point(0.03, $erosion3, 0.0),
            new Point(0.06, $erosion4, 0.0),
        ]);

        return self::$cachedSpline;
    }
}