<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

use lolfosjo\voverworld\generator\noise\minecraft\perlin\OctavePerlinNoiseSampler;
use lolfosjo\voverworld\util\math\RandomSourceProvider;
use lolfosjo\voverworld\generator\densityfunction\OldBlendedNoise;

final class DensityBase3dNoise
{
    private function __construct() {}

    public static function oldBlendedNoise(
        RandomSourceProvider $random,
        float $xzScale,
        float $yScale,
        float $xzFactor,
        float $yFactor,
        float $smearScaleMultiplier
    ): DensityFunction {
        $limitOctaves = self::descendingOctaves(-15, 0);
        $mainOctaves = self::descendingOctaves(-7, 0);

        return new OldBlendedNoise(
            OctavePerlinNoiseSampler::createFromOctaves($random->fork(), $limitOctaves),
            OctavePerlinNoiseSampler::createFromOctaves($random->fork(), $limitOctaves),
            OctavePerlinNoiseSampler::createFromOctaves($random->fork(), $mainOctaves),
            $xzScale,
            $yScale,
            $xzFactor,
            $yFactor,
            $smearScaleMultiplier
        );
    }

    public static function overworld(RandomSourceProvider $random): DensityFunction
    {
        return self::oldBlendedNoise($random, 0.25, 0.125, 80.0, 160.0, 8.0);
    }

    private static function descendingOctaves(int $minInclusive, int $maxInclusive): array
    {
        return range($maxInclusive, $minInclusive, -1);
    }
}