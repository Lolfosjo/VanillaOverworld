<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class JaggednessSpline
{
    private static ?Spline $cachedSpline = null;

    public static function getCachedSpline(): Spline
    {
        if (self::$cachedSpline !== null) {
            return self::$cachedSpline;
        }

        // Ridges Splines 
        $ridges1 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.63), 0.0),
            new Point(0.01, new StaticValue(0.3), 0.0),
        ]);

        $ridges2 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.315), 0.0),
            new Point(0.01, new StaticValue(0.15), 0.0),
        ]);

        $ridges3 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.315), 0.0),
            new Point(0.01, new StaticValue(0.15), 0.0),
        ]);

        $ridges4 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.63), 0.0),
            new Point(0.01, new StaticValue(0.3), 0.0),
        ]);

        $ridges5 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.63), 0.0),
            new Point(0.01, new StaticValue(0.3), 0.0),
        ]);

        $ridges6 = new Spline("minecraft:overworld/ridges", [
            new Point(-0.01, new StaticValue(0.63), 0.0),
            new Point(0.01, new StaticValue(0.3), 0.0),
        ]);

        // Ridges Folded Splines
        $ridgesFolded1 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.19999999, new StaticValue(0.0), 0.0),
            new Point(0.44999996, new StaticValue(0.0), 0.0),
            new Point(1.0, $ridges1, 0.0),
        ]);

        $ridgesFolded2 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.19999999, new StaticValue(0.0), 0.0),
            new Point(0.44999996, new StaticValue(0.0), 0.0),
            new Point(1.0, $ridges2, 0.0),
        ]);

        $ridgesFolded3 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.19999999, new StaticValue(0.0), 0.0),
            new Point(0.44999996, new StaticValue(0.0), 0.0),
            new Point(1.0, $ridges3, 0.0),
        ]);

        $ridgesFolded4 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.19999999, new StaticValue(0.0), 0.0),
            new Point(0.44999996, $ridges4, 0.0),
            new Point(1.0, $ridges5, 0.0),
        ]);

        $ridgesFolded5 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(0.19999999, new StaticValue(0.0), 0.0),
            new Point(0.44999996, new StaticValue(0.0), 0.0),
            new Point(1.0, $ridges6, 0.0),
        ]);

        // Erosion Splines
        $erosion1 = new Spline("minecraft:overworld/erosion", [
            new Point(-1.0, $ridgesFolded1, 0.0),
            new Point(-0.78, $ridgesFolded2, 0.0),
            new Point(-0.5775, $ridgesFolded3, 0.0),
            new Point(-0.375, new StaticValue(0.0), 0.0),
        ]);

        $erosion2 = new Spline("minecraft:overworld/erosion", [
            new Point(-1.0, $ridgesFolded4, 0.0),
            new Point(-0.78, $ridgesFolded5, 0.0),
            new Point(-0.5775, $ridgesFolded5, 0.0),
            new Point(-0.375, new StaticValue(0.0), 0.0),
        ]);

        // Final Cached Spline
        self::$cachedSpline = new Spline("minecraft:overworld/continents", [
            new Point(-0.11, new StaticValue(0.0), 0.0),
            new Point(0.03, $erosion1, 0.0),
            new Point(0.65, $erosion2, 0.0),
        ]);

        return self::$cachedSpline;
    }
}