<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class StaticValue implements IEvaluator
{
    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function evaluate(array $parameters): float
    {
        return $this->value;
    }
}