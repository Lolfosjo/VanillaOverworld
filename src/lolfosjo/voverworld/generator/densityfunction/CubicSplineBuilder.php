<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class CubicSplineBuilder
{
    /** @var Point[] */
    private array $points = [];

    /**
     * @param mixed $coordinate
     */
    public function __construct(
        private readonly mixed $coordinate
    ) {
    }

    /**
     * Add a point with a constant value.
     */
    public function addPoint(float $location, float $value, float $derivative): self
    {
        return $this->addPointValue($location, new ConstantValue($value), $derivative);
    }

    /**
     * Add a point with a custom Value provider.
     */
    public function addPointValue(float $location, Value $value, float $derivative): self
    {
        $this->points[] = new Point($location, $value, $derivative);
        return $this;
    }

    public function build(): CubicSpline
    {
        return new CubicSpline($this->coordinate, $this->points);
    }
}