<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Noise extends AbstractDensityFunction {
    private NoiseHolder $noise;
    private float $xzScale;
    private float $yScale;
    public function __construct(NoiseHolder $noise, float $xzScale, float $yScale) {
        $this->noise = $noise;
        $this->xzScale = $xzScale;
        $this->yScale = $yScale;
    }
    public function compute(FunctionContext $context): float {
        return $this->noise->getValue(
            $context->blockX() * $this->xzScale,
            $context->blockY() * $this->yScale,
            $context->blockZ() * $this->xzScale
        );
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }
    public function minValue(): float { return -$this->maxValue(); }
    public function maxValue(): float { return $this->noise->maxValue(); }
}