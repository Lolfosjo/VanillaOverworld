<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class RangeChoice extends AbstractDensityFunction {
    private DensityFunction $input;
    private float $minInclusive;
    private float $maxExclusive;
    private DensityFunction $whenInRange;
    private DensityFunction $whenOutOfRange;
    public function __construct(
        DensityFunction $input,
        float $minInclusive,
        float $maxExclusive,
        DensityFunction $whenInRange,
        DensityFunction $whenOutOfRange
    ) {
        $this->input = $input;
        $this->minInclusive = $minInclusive;
        $this->maxExclusive = $maxExclusive;
        $this->whenInRange = $whenInRange;
        $this->whenOutOfRange = $whenOutOfRange;
    }
    public function compute(FunctionContext $context): float {
        $val = $this->input->compute($context);
        if ($val >= $this->minInclusive && $val < $this->maxExclusive) {
            return $this->whenInRange->compute($context);
        }
        return $this->whenOutOfRange->compute($context);
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $this->input->fillArray($output, $contextProvider);
        for ($i = 0; $i < count($output); $i++) {
            $val = $output[$i];
            $ctx = $contextProvider->forIndex($i);
            $output[$i] = ($val >= $this->minInclusive && $val < $this->maxExclusive)
                ? $this->whenInRange->compute($ctx)
                : $this->whenOutOfRange->compute($ctx);
        }
    }
    public function minValue(): float {
        return \min($this->whenInRange->minValue(), $this->whenOutOfRange->minValue());
    }
    public function maxValue(): float {
        return \max($this->whenInRange->maxValue(), $this->whenOutOfRange->maxValue());
    }
}