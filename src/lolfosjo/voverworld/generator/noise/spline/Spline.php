<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

class Spline implements IEvaluator
{
    /** @var Point[] */
    private array $points;
    private string $coordinate;

    /**
     * @param Point[] $points
     * @throws \InvalidArgumentException if fewer than 2 points are provided.
     */
    public function __construct(string $coordinate, array $points)
    {
        if (count($points) < 2) {
            throw new \InvalidArgumentException('Spline needs at least two points');
        }
        $this->points = $points;
        $this->coordinate = $coordinate;
    }

    public function evaluate(array $parameters): float
    {
        $input = $parameters[$this->coordinate] ?? 0.0;

        $count = count($this->points);
        for ($i = 0; $i < $count - 1; $i++) {
            $p0 = $this->points[$i];
            $p1 = $this->points[$i + 1];

            if ($input >= $p0->location && $input <= $p1->location) {
                $y0 = $p0->value->evaluate($parameters);
                $y1 = $p1->value->evaluate($parameters);
                return $this->hermite(
                    $input,
                    $p0->location, $y0, $p0->derivative,
                    $p1->location, $y1, $p1->derivative
                );
            }
        }

        if ($input < $this->points[0]->location) {
            return $this->points[0]->value->evaluate($parameters);
        }
        return $this->points[$count - 1]->value->evaluate($parameters);
    }

    private function hermite(
        float $x,
        float $x0, float $y0, float $m0,
        float $x1, float $y1, float $m1
    ): float {
        $t = ($x - $x0) / ($x1 - $x0);
        $t2 = $t * $t;
        $t3 = $t2 * $t;

        $h00 = 2 * $t3 - 3 * $t2 + 1;
        $h10 = $t3 - 2 * $t2 + $t;
        $h01 = -2 * $t3 + 3 * $t2;
        $h11 = $t3 - $t2;

        return $h00 * $y0 + $h10 * ($x1 - $x0) * $m0 + $h01 * $y1 + $h11 * ($x1 - $x0) * $m1;
    }
}