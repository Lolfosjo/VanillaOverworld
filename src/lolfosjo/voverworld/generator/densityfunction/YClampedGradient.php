<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class YClampedGradient extends AbstractDensityFunction {
    private int $fromY;
    private int $toY;
    private float $fromValue;
    private float $toValue;
    public function __construct(int $fromY, int $toY, float $fromValue, float $toValue) {
        $this->fromY = $fromY;
        $this->toY = $toY;
        $this->fromValue = $fromValue;
        $this->toValue = $toValue;
    }
    public function compute(FunctionContext $context): float {
        return self::clampedMap($context->blockY(), $this->fromY, $this->toY, $this->fromValue, $this->toValue);
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }
    public function minValue(): float { return \min($this->fromValue, $this->toValue); }
    public function maxValue(): float { return \max($this->fromValue, $this->toValue); }
    private static function clampedMap(float $value, float $fromY, float $toY, float $fromValue, float $toValue): float {
        if ($fromY == $toY) return $value < $fromY ? $fromValue : $toValue;
        $t = \max(0.0, \min(1.0, ($value - $fromY) / ($toY - $fromY)));
        return $fromValue + $t * ($toValue - $fromValue);
    }
}