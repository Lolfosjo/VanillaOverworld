<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Ap2 extends AbstractDensityFunction implements TwoArgumentSimpleFunction {
    private TwoArgumentType $type;
    private DensityFunction $arg1;
    private DensityFunction $arg2;
    private float $minValue;
    private float $maxValue;
    public function __construct(TwoArgumentType $type, DensityFunction $arg1, DensityFunction $arg2, float $minValue, float $maxValue) {
        $this->type = $type;
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }
    public function compute(FunctionContext $context): float {
        $v1 = $this->arg1->compute($context);
        return match ($this->type) {
            TwoArgumentType::ADD => $v1 + $this->arg2->compute($context),
            TwoArgumentType::MUL => $v1 == 0.0 ? 0.0 : $v1 * $this->arg2->compute($context),
            TwoArgumentType::MIN => $v1 < $this->arg2->minValue() ? $v1 : \min($v1, $this->arg2->compute($context)),
            TwoArgumentType::MAX => $v1 > $this->arg2->maxValue() ? $v1 : \max($v1, $this->arg2->compute($context)),
        };
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $this->arg1->fillArray($output, $contextProvider);
        switch ($this->type) {
            case TwoArgumentType::ADD:
                $other = [];
                $this->arg2->fillArray($other, $contextProvider);
                foreach ($output as $i => $v) $output[$i] = $v + $other[$i];
                break;
            case TwoArgumentType::MUL:
                for ($i = 0; $i < count($output); $i++) {
                    $v = $output[$i];
                    $output[$i] = $v == 0.0 ? 0.0 : $v * $this->arg2->compute($contextProvider->forIndex($i));
                }
                break;
            case TwoArgumentType::MIN:
                $min = $this->arg2->minValue();
                for ($i = 0; $i < count($output); $i++) {
                    $v = $output[$i];
                    $output[$i] = $v < $min ? $v : \min($v, $this->arg2->compute($contextProvider->forIndex($i)));
                }
                break;
            case TwoArgumentType::MAX:
                $max = $this->arg2->maxValue();
                for ($i = 0; $i < count($output); $i++) {
                    $v = $output[$i];
                    $output[$i] = $v > $max ? $v : \max($v, $this->arg2->compute($contextProvider->forIndex($i)));
                }
                break;
        }
    }
    public function minValue(): float { return $this->minValue; }
    public function maxValue(): float { return $this->maxValue; }
}