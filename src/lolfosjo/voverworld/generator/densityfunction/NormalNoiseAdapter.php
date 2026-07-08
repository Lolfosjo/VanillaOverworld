<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

use lolfosjo\voverworld\generator\noise\minecraft\noise\NormalNoise;

class NormalNoiseAdapter implements NoiseHolder {
    private NormalNoise $noise;
    public function __construct(NormalNoise $noise) {
        $this->noise = $noise;
    }
    public function getValue(float $x, float $y, float $z): float {
        return $this->noise->getValue($x, $y, $z);
    }
    public function maxValue(): float {
        return $this->noise->getMax();
    }
}