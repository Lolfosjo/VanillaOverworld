<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Clamp extends AbstractDensityFunction implements PureTransformer {
    private DensityFunction $input;
    private float $min;
    private float $max;
    public function __construct(DensityFunction $input, float $min, float $max) {
        $this->input = $input;
        $this->min = $min;
        $this->max = $max;
    }
    public function input(): DensityFunction { return $this->input; }
    public function transform(float $inputValue): float {
        return \max($this->min, \min($this->max, $inputValue));
    }
    public function compute(FunctionContext $context): float {
        return $this->transform($this->input->compute($context));
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $this->input->fillArray($output, $contextProvider);
        foreach ($output as &$v) $v = $this->transform($v);
    }
    public function minValue(): float { return $this->min; }
    public function maxValue(): float { return $this->max; }
}