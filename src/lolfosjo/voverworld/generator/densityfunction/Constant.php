<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Constant extends AbstractDensityFunction {
    private float $value;
    public function __construct(float $value) {
        $this->value = $value;
    }
    public function compute(FunctionContext $context): float {
        return $this->value;
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $output = array_fill(0, count($output), $this->value);
    }
    public function minValue(): float { return $this->value; }
    public function maxValue(): float { return $this->value; }
}