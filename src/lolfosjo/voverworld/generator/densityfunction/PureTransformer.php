<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface PureTransformer extends DensityFunction {
    public function input(): DensityFunction;
    public function transform(float $inputValue): float;
}