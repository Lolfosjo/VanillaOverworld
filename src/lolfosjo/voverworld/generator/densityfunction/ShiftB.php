<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class ShiftB extends Shift {
    public function __construct(NoiseHolder $offsetNoise) {
        parent::__construct($offsetNoise);
    }
    public function compute(FunctionContext $context): float {
        return $this->computeShift($context->blockZ(), $context->blockX(), 0.0);
    }
}