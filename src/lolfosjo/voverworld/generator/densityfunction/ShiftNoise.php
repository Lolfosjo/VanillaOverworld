<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface ShiftNoise extends DensityFunction {
    public function offsetNoise(): NoiseHolder;
    public function computeShift(float $x, float $y, float $z): float;
}