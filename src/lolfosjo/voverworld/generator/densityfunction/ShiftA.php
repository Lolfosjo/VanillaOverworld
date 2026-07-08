<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class ShiftA extends Shift {
    public function __construct(NoiseHolder $offsetNoise) {
        parent::__construct($offsetNoise);
    }
    public function compute(FunctionContext $context): float {
        return $this->computeShift($context->blockX(), 0.0, $context->blockZ());
    }
}