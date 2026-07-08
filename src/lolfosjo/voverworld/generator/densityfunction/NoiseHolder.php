<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface NoiseHolder {
    public function getValue(float $x, float $y, float $z): float;
    public function maxValue(): float;
}