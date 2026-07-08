<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Shift extends AbstractDensityFunction implements ShiftNoise {
    private NoiseHolder $offsetNoise;
    public function __construct(NoiseHolder $offsetNoise) {
        $this->offsetNoise = $offsetNoise;
    }
    public function offsetNoise(): NoiseHolder { return $this->offsetNoise; }
    public function computeShift(float $x, float $y, float $z): float {
        return $this->offsetNoise->getValue($x * 0.25, $y * 0.25, $z * 0.25) * 4.0;
    }
    public function compute(FunctionContext $context): float {
        return $this->computeShift($context->blockX(), $context->blockY(), $context->blockZ());
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }
    public function minValue(): float { return -$this->maxValue(); }
    public function maxValue(): float { return $this->offsetNoise->maxValue() * 4.0; }
}