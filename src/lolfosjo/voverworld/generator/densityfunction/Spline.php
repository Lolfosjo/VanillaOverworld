<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Spline extends AbstractDensityFunction {
    private CubicSpline $spline;
    private float $minValue;
    private float $maxValue;
    public function __construct(CubicSpline $spline) {
        $this->spline = $spline;
        $this->minValue = $spline->minValue();
        $this->maxValue = $spline->maxValue();
    }
    public function compute(FunctionContext $context): float {
        // Create a Point object that stores the context
        $point = new SplineContextPoint($context);
        return $this->spline->apply($point);
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }
    public function minValue(): float { return $this->minValue; }
    public function maxValue(): float { return $this->maxValue; }
}

// Helper class for the context
class SplineContextPoint {
    private FunctionContext $context;
    public function __construct(FunctionContext $context) {
        $this->context = $context;
    }
    public function context(): FunctionContext {
        return $this->context;
    }
}