<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

use lolfosjo\voverworld\generator\noise\minecraft\perlin\OctavePerlinNoiseSampler;

class OldBlendedNoise extends AbstractDensityFunction
{
    private const MAIN_NOISE_DIVISOR = 10.0;
    private const LIMIT_NOISE_DIVISOR = 512.0;
    private const RESULT_DIVISOR = 128.0;
    private const BASE_SCALE = 684.412;

    private OctavePerlinNoiseSampler $minLimitNoise;
    private OctavePerlinNoiseSampler $maxLimitNoise;
    private OctavePerlinNoiseSampler $mainNoise;
    private float $xzScale;
    private float $yScale;
    private float $xzFactor;
    private float $yFactor;
    private float $smearScaleMultiplier;

    public function __construct(
        OctavePerlinNoiseSampler $minLimitNoise,
        OctavePerlinNoiseSampler $maxLimitNoise,
        OctavePerlinNoiseSampler $mainNoise,
        float $xzScale,
        float $yScale,
        float $xzFactor,
        float $yFactor,
        float $smearScaleMultiplier
    ) {
        $this->minLimitNoise = $minLimitNoise;
        $this->maxLimitNoise = $maxLimitNoise;
        $this->mainNoise = $mainNoise;
        $this->xzScale = $xzScale;
        $this->yScale = $yScale;
        $this->xzFactor = $xzFactor;
        $this->yFactor = $yFactor;
        $this->smearScaleMultiplier = $smearScaleMultiplier;
    }

    public function compute(FunctionContext $context): float
    {
        $baseScale = self::BASE_SCALE;
        $scaledXZ = $baseScale * $this->xzScale;
        $scaledY = $baseScale * $this->yScale;
        $mainScaledXZ = $scaledXZ / $this->xzFactor;
        $mainScaledY = $scaledY / $this->yFactor;
        $smearScale = $scaledY * $this->smearScaleMultiplier;

        $x = $context->blockX();
        $y = $context->blockY();
        $z = $context->blockZ();

        // Main noise contribution
        $mainValue = 0.0;
        $frequency = 1.0;
        $mainNoiseCount = $this->mainNoise->getCount();
        for ($octave = 0; $octave < $mainNoiseCount; $octave++) {
            $sampler = $this->mainNoise->getOctave($octave);
            if ($sampler !== null) {
                $mainValue += $sampler->sample(
                    $x * $mainScaledXZ * $frequency,
                    $y * $mainScaledY * $frequency,
                    $z * $mainScaledXZ * $frequency,
                    $smearScale * $frequency,
                    $y * $mainScaledY * $frequency
                ) / $frequency;
            }
            $frequency /= 2.0;
        }

        // Blend factor for limit noises
        $blend = \max(0.0, \min(2.0, $mainValue / self::MAIN_NOISE_DIVISOR + 1.0)) * 0.5;
        $useOnlyMax = $blend >= 1.0;
        $useOnlyMin = $blend <= 0.0;

        // Limit noises
        $minValue = 0.0;
        $maxValue = 0.0;
        $frequency = 1.0;
        $octaveCount = \min($this->minLimitNoise->getCount(), $this->maxLimitNoise->getCount());
        for ($octave = 0; $octave < $octaveCount; $octave++) {
            $sampleX = $x * $scaledXZ * $frequency;
            $sampleY = $y * $scaledY * $frequency;
            $sampleZ = $z * $scaledXZ * $frequency;
            $sampleSmear = $smearScale * $frequency;

            if (!$useOnlyMax) {
                $sampler = $this->minLimitNoise->getOctave($octave);
                if ($sampler !== null) {
                    $minValue += $sampler->sample(
                        $sampleX,
                        $sampleY,
                        $sampleZ,
                        $sampleSmear,
                        $sampleY
                    ) / $frequency;
                }
            }
            if (!$useOnlyMin) {
                $sampler = $this->maxLimitNoise->getOctave($octave);
                if ($sampler !== null) {
                    $maxValue += $sampler->sample(
                        $sampleX,
                        $sampleY,
                        $sampleZ,
                        $sampleSmear,
                        $sampleY
                    ) / $frequency;
                }
            }
            $frequency /= 2.0;
        }

        $lower = $minValue / self::LIMIT_NOISE_DIVISOR;
        $upper = $maxValue / self::LIMIT_NOISE_DIVISOR;
        return $this->lerp($lower, $upper, $blend) / self::RESULT_DIVISOR;
    }

    public function fillArray(array &$output, ContextProvider $contextProvider): void
    {
        $contextProvider->fillAllDirectly($output, $this);
    }

    public function minValue(): float
    {
        return -1.5;
    }

    public function maxValue(): float
    {
        return 1.5;
    }

    private function lerp(float $first, float $second, float $alpha): float
    {
        return $first + $alpha * ($second - $first);
    }
}