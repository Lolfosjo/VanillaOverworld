<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class MulOrAdd extends AbstractDensityFunction implements TwoArgumentSimpleFunction, PureTransformer {
    private MulOrAddType $type;
    private DensityFunction $input;
    private float $argument;
    private float $minValue;
    private float $maxValue;
    public function __construct(MulOrAddType $type, DensityFunction $input, float $minValue, float $maxValue, float $argument) {
        $this->type = $type;
        $this->input = $input;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->argument = $argument;
    }
    public function input(): DensityFunction { return $this->input; }
    public function transform(float $inputValue): float {
        return match ($this->type) {
            MulOrAddType::MUL => $inputValue * $this->argument,
            MulOrAddType::ADD => $inputValue + $this->argument,
        };
    }
    public function compute(FunctionContext $context): float {
        return $this->transform($this->input->compute($context));
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $this->input->fillArray($output, $contextProvider);
        foreach ($output as &$v) $v = $this->transform($v);
    }
    public function minValue(): float { return $this->minValue; }
    public function maxValue(): float { return $this->maxValue; }
}

enum MulOrAddType {
    case MUL;
    case ADD;
}