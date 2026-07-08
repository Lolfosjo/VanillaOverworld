<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class ShiftedNoise extends AbstractDensityFunction {
    private DensityFunction $shiftX;
    private DensityFunction $shiftY;
    private DensityFunction $shiftZ;
    private float $xzScale;
    private float $yScale;
    private NoiseHolder $noise;
    public function __construct(
        DensityFunction $shiftX,
        DensityFunction $shiftY,
        DensityFunction $shiftZ,
        float $xzScale,
        float $yScale,
        NoiseHolder $noise
    ) {
        $this->shiftX = $shiftX;
        $this->shiftY = $shiftY;
        $this->shiftZ = $shiftZ;
        $this->xzScale = $xzScale;
        $this->yScale = $yScale;
        $this->noise = $noise;
    }
    public function compute(FunctionContext $context): float {
        $x = $context->blockX() * $this->xzScale + $this->shiftX->compute($context);
        $y = $context->blockY() * $this->yScale + $this->shiftY->compute($context);
        $z = $context->blockZ() * $this->xzScale + $this->shiftZ->compute($context);
        return $this->noise->getValue($x, $y, $z);
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }
    public function minValue(): float { return -$this->maxValue(); }
    public function maxValue(): float { return $this->noise->maxValue(); }
}