<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class CubicSpline
{
    /** @var float[] */
    private array $locations;

    /** @var Value[] */
    private array $values;

    /** @var float[] */
    private array $derivatives;

    private float $minValue;
    private float $maxValue;

    /**
     * @param callable(mixed): float $coordinate
     * @param Point[]                $points
     */
    public function __construct(
        private readonly mixed $coordinate,
        array $points
    ) {
        if (count($points) < 2) {
            throw new \InvalidArgumentException('CubicSpline needs at least two points');
        }

        // Sort points by location
        usort($points, static fn(Point $a, Point $b) => $a->location <=> $b->location);
        $sortedPoints = array_values($points);

        $pointCount = count($sortedPoints);
        $this->locations = array_fill(0, $pointCount, 0.0);
        $this->values = array_fill(0, $pointCount, null);
        $this->derivatives = array_fill(0, $pointCount, 0.0);

        $min = INF;
        $max = -INF;
        foreach ($sortedPoints as $i => $point) {
            $this->locations[$i] = $point->location;
            $this->values[$i] = $point->value;
            $this->derivatives[$i] = $point->derivative;
            $min = min($min, $point->value->minValue());
            $max = max($max, $point->value->maxValue());
        }
        $this->minValue = $min;
        $this->maxValue = $max;
    }

    public static function builder(callable $coordinate): CubicSplineBuilder
    {
        return new CubicSplineBuilder($coordinate);
    }

    public static function constant(float $value): Value
    {
        return new ConstantValue($value);
    }

    public function apply(mixed $input): float
    {
        $x = ($this->coordinate)($input);
        $range = $this->findRangeForLocation($this->locations, $x);

        if ($range < 0) {
            return $this->values[0]->apply($input);
        }

        $last = count($this->locations) - 1;
        if ($range === $last) {
            return $this->values[$last]->apply($input);
        }

        $loc0 = $this->locations[$range];
        $loc1 = $this->locations[$range + 1];
        $locDist = $loc1 - $loc0;
        $k = ($x - $loc0) / $locDist;

        $y0 = $this->values[$range]->apply($input);
        $y1 = $this->values[$range + 1]->apply($input);
        $yDist = $y1 - $y0;

        $p = $this->derivatives[$range] * $locDist - $yDist;
        $q = -$this->derivatives[$range + 1] * $locDist + $yDist;

        return $y0 + $k * $yDist + $k * (1.0 - $k) * ($p + $k * ($q - $p));
    }

    public function minValue(): float
    {
        return $this->minValue;
    }

    public function maxValue(): float
    {
        return $this->maxValue;
    }

    /**
     * Binary search for the largest index i with locations[i] <= x.
     * Returns -1 if x < first location, or last index if x >= last location.
     *
     * @param float[] $locations
     */
    private function findRangeForLocation(array $locations, float $x): int
    {
        $min = 0;
        $length = count($locations);

        while ($length > 0) {
            $half = $length >> 1;
            $mid = $min + $half;
            if ($x < $locations[$mid]) {
                $length = $half;
            } else {
                $min = $mid + 1;
                $length -= $half + 1;
            }
        }

        return $min - 1;
    }
}