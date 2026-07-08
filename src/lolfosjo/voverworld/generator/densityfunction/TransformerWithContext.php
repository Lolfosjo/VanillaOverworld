<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface TransformerWithContext extends DensityFunction {
    public function input(): DensityFunction;
    public function transform(FunctionContext $context, float $inputValue): float;
}