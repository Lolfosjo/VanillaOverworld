<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class OffsetSpline
{
    private static ?Spline $cachedSpline = null;

    public static function getCachedSpline(): Spline
    {
        if (self::$cachedSpline !== null) {
            return self::$cachedSpline;
        }

        // Ridges Folded Splines (1–26)
        $ridgesFolded1 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.08880186), 0.38940096),
            new Point(1.0, new StaticValue(0.69000006), 0.38940096),
        ]);

        $ridgesFolded2 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.115760356), 0.37788022),
            new Point(1.0, new StaticValue(0.6400001), 0.37788022),
        ]);

        $ridgesFolded3 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.2222), 0.0),
            new Point(-0.75, new StaticValue(-0.2222), 0.0),
            new Point(-0.65, new StaticValue(0.0), 0.0),
            new Point(0.5954547, new StaticValue(2.9802322E-8), 0.0),
            new Point(0.6054547, new StaticValue(2.9802322E-8), 0.2534563),
            new Point(1.0, new StaticValue(0.100000024), 0.2534563),
        ]);

        $ridgesFolded4 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.3), 0.5),
            new Point(-0.4, new StaticValue(0.05), 0.0),
            new Point(0.0, new StaticValue(0.05), 0.0),
            new Point(0.4, new StaticValue(0.05), 0.0),
            new Point(1.0, new StaticValue(0.060000002), 0.007000001),
        ]);

        $ridgesFolded5 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.15), 0.5),
            new Point(-0.4, new StaticValue(0.0), 0.0),
            new Point(0.0, new StaticValue(0.0), 0.0),
            new Point(0.4, new StaticValue(0.05), 0.1),
            new Point(1.0, new StaticValue(0.060000002), 0.007000001),
        ]);

        $ridgesFolded6 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.15), 0.5),
            new Point(-0.4, new StaticValue(0.0), 0.0),
            new Point(0.0, new StaticValue(0.0), 0.0),
            new Point(0.4, new StaticValue(0.0), 0.0),
            new Point(1.0, new StaticValue(0.0), 0.0),
        ]);

        $ridgesFolded7 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.02), 0.0),
            new Point(-0.4, new StaticValue(-0.03), 0.0),
            new Point(0.0, new StaticValue(-0.03), 0.0),
            new Point(0.4, new StaticValue(0.0), 0.06),
            new Point(1.0, new StaticValue(0.0), 0.0),
        ]);

        $ridgesFolded8 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.25), 0.5),
            new Point(-0.4, new StaticValue(0.05), 0.0),
            new Point(0.0, new StaticValue(0.05), 0.0),
            new Point(0.4, new StaticValue(0.05), 0.0),
            new Point(1.0, new StaticValue(0.060000002), 0.007000001),
        ]);

        $ridgesFolded9 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.1), 0.5),
            new Point(-0.4, new StaticValue(0.001), 0.01),
            new Point(0.0, new StaticValue(0.003), 0.01),
            new Point(0.4, new StaticValue(0.05), 0.094000004),
            new Point(1.0, new StaticValue(0.060000002), 0.007000001),
        ]);

        $ridgesFolded10 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.1), 0.5),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded11 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.02), 0.015),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded12 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(0.20235021), 0.0),
            new Point(0.0, new StaticValue(0.7161751), 0.5138249),
            new Point(1.0, new StaticValue(1.23), 0.5138249),
        ]);

        $ridgesFolded13 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(0.2), 0.0),
            new Point(0.0, new StaticValue(0.44682026), 0.43317974),
            new Point(1.0, new StaticValue(0.88), 0.43317974),
        ]);

        $ridgesFolded14 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(0.2), 0.0),
            new Point(0.0, new StaticValue(0.30829495), 0.3917051),
            new Point(1.0, new StaticValue(0.70000005), 0.3917051),
        ]);

        $ridgesFolded15 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.25), 0.5),
            new Point(-0.4, new StaticValue(0.35), 0.0),
            new Point(0.0, new StaticValue(0.35), 0.0),
            new Point(0.4, new StaticValue(0.35), 0.0),
            new Point(1.0, new StaticValue(0.42000002), 0.049000014),
        ]);

        $ridgesFolded16 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.1), 0.5),
            new Point(-0.4, new StaticValue(0.0069999998), 0.07),
            new Point(0.0, new StaticValue(0.021), 0.07),
            new Point(0.4, new StaticValue(0.35), 0.658),
            new Point(1.0, new StaticValue(0.42000002), 0.049000014),
        ]);

        $ridgesFolded17 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.1), 0.5),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded18 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.05), 0.5),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded19 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(0.2), 0.0),
            new Point(0.0, new StaticValue(0.5391705), 0.4608295),
            new Point(1.0, new StaticValue(1.0), 0.4608295),
        ]);

        $ridgesFolded20 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.2), 0.5),
            new Point(-0.4, new StaticValue(0.5), 0.0),
            new Point(0.0, new StaticValue(0.5), 0.0),
            new Point(0.4, new StaticValue(0.5), 0.0),
            new Point(1.0, new StaticValue(0.6), 0.070000015),
        ]);

        $ridgesFolded21 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.05), 0.5),
            new Point(-0.4, new StaticValue(0.01), 0.099999994),
            new Point(0.0, new StaticValue(0.03), 0.099999994),
            new Point(0.4, new StaticValue(0.5), 0.94),
            new Point(1.0, new StaticValue(0.6), 0.070000015),
        ]);

        $ridgesFolded22 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.05), 0.5),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded23 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.02), 0.015),
            new Point(-0.4, new StaticValue(0.01), 0.0),
            new Point(0.0, new StaticValue(0.01), 0.0),
            new Point(0.4, new StaticValue(0.03), 0.04),
            new Point(1.0, new StaticValue(0.1), 0.049),
        ]);

        $ridgesFolded24 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(0.34792626), 0.0),
            new Point(0.0, new StaticValue(0.9239631), 0.5760369),
            new Point(1.0, new StaticValue(1.5), 0.5760369),
        ]);

        $ridgesFolded25 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.1), 0.0),
            new Point(-0.4, new StaticValue(0.1), 0.0),
            new Point(0.0, new StaticValue(0.17), 0.0),
        ]);

        $ridgesFolded26 = new Spline("minecraft:overworld/ridges_folded", [
            new Point(-1.0, new StaticValue(-0.05), 0.0),
            new Point(-0.4, new StaticValue(0.1), 0.0),
            new Point(0.0, new StaticValue(0.17), 0.0),
        ]);

        // Erosion Splines
        $erosion1 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.85, $ridgesFolded1, 0.0),
            new Point(-0.7, $ridgesFolded2, 0.0),
            new Point(-0.4, $ridgesFolded3, 0.0),
            new Point(-0.35, $ridgesFolded4, 0.0),
            new Point(-0.1, $ridgesFolded5, 0.0),
            new Point(0.2, $ridgesFolded6, 0.0),
            new Point(0.7, $ridgesFolded7, 0.0),
        ]);

        $erosion2 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.85, $ridgesFolded1, 0.0),
            new Point(-0.7, $ridgesFolded2, 0.0),
            new Point(-0.4, $ridgesFolded3, 0.0),
            new Point(-0.35, $ridgesFolded8, 0.0),
            new Point(-0.1, $ridgesFolded9, 0.0),
            new Point(0.2, $ridgesFolded10, 0.0),
            new Point(0.7, $ridgesFolded11, 0.0),
        ]);

        $erosion3 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.85, $ridgesFolded12, 0.0),
            new Point(-0.7, $ridgesFolded13, 0.0),
            new Point(-0.4, $ridgesFolded14, 0.0),
            new Point(-0.35, $ridgesFolded15, 0.0),
            new Point(-0.1, $ridgesFolded16, 0.0),
            new Point(0.2, $ridgesFolded17, 0.0),
            new Point(0.4, $ridgesFolded17, 0.0),
            new Point(0.45, $ridgesFolded25, 0.0),
            new Point(0.55, $ridgesFolded25, 0.0),
            new Point(0.58, $ridgesFolded18, 0.0),
            new Point(0.7, $ridgesFolded11, 0.0),
        ]);

        $erosion4 = new Spline("minecraft:overworld/erosion", [
            new Point(-0.85, $ridgesFolded24, 0.0),
            new Point(-0.7, $ridgesFolded19, 0.0),
            new Point(-0.4, $ridgesFolded19, 0.0),
            new Point(-0.35, $ridgesFolded20, 0.0),
            new Point(-0.1, $ridgesFolded21, 0.0),
            new Point(0.2, $ridgesFolded22, 0.0),
            new Point(0.4, $ridgesFolded22, 0.0),
            new Point(0.45, $ridgesFolded26, 0.0),
            new Point(0.55, $ridgesFolded26, 0.0),
            new Point(0.58, $ridgesFolded22, 0.0),
            new Point(0.7, $ridgesFolded23, 0.0),
        ]);

        // Final Cached Spline
        self::$cachedSpline = new Spline("minecraft:overworld/continents", [
            new Point(-1.1, new StaticValue(0.044), 0.0),
            new Point(-1.02, new StaticValue(-0.2222), 0.0),
            new Point(-0.51, new StaticValue(-0.2222), 0.0),
            new Point(-0.44, new StaticValue(-0.12), 0.0),
            new Point(-0.18, new StaticValue(-0.12), 0.0),
            new Point(-0.16, $erosion1, 0.0),
            new Point(-0.15, $erosion1, 0.0),
            new Point(-0.1, $erosion2, 0.0),
            new Point(0.25, $erosion3, 0.0),
            new Point(1.0, $erosion4, 0.0),
        ]);

        return self::$cachedSpline;
    }
}