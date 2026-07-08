<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class Point
{
    public float $location;
    public IEvaluator $value;
    public float $derivative;

    public function __construct(float $location, IEvaluator $value, float $derivative)
    {
        $this->location = $location;
        $this->value = $value;
        $this->derivative = $derivative;
    }

    public static function fromValue(float $location, float $value, float $derivative): self
    {
        return new self($location, new StaticValue($value), $derivative);
    }
}