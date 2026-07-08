<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface DensityFunction {
    public function compute(FunctionContext $context): float;
    public function fillArray(array &$output, ContextProvider $contextProvider): void;
    public function minValue(): float;
    public function maxValue(): float;
}