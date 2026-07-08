<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class SplineCoordinate implements Value {
    private DensityFunction $function;
    public function __construct(DensityFunction $function) {
        $this->function = $function;
    }
    public function apply($input): float {
        if ($input instanceof SplineContextPoint) {
            return $this->function->compute($input->context());
        }
        return 0.0;
    }
    public function minValue(): float {
        return $this->function->minValue();
    }
    public function maxValue(): float {
        return $this->function->maxValue();
    }
}