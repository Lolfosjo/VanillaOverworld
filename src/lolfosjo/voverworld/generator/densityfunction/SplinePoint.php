<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class SplinePoint {
    public float $location;
    public $value; // float or DensityFunction
    public float $derivative;
    public function __construct(float $location, $value, float $derivative) {
        $this->location = $location;
        $this->value = $value;
        $this->derivative = $derivative;
    }
}