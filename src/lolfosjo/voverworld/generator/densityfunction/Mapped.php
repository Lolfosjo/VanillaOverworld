<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Mapped extends AbstractDensityFunction implements PureTransformer {
    private MappedType $type;
    private DensityFunction $input;
    private float $minValue;
    private float $maxValue;

    public function __construct(MappedType $type, DensityFunction $input, float $minValue, float $maxValue) {
        $this->type = $type;
        $this->input = $input;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public static function create(MappedType $type, DensityFunction $input): self {
        $min = $input->minValue();
        $max = $input->maxValue();
        $tMin = self::transformType($type, $min);
        $tMax = self::transformType($type, $max);
        if ($type === MappedType::INVERT) {
            if ($min < 0.0 && $max > 0.0) {
                return new self($type, $input, -INF, INF);
            }
            return new self($type, $input, \min($tMin, $tMax), \max($tMin, $tMax));
        }
        if ($type === MappedType::ABS || $type === MappedType::SQUARE) {
            return new self($type, $input, \max(0.0, \min($tMin, $tMax)), \max($tMin, $tMax));
        }
        return new self($type, $input, \min($tMin, $tMax), \max($tMin, $tMax));
    }

    private static function transformType(MappedType $type, float $value): float {
        return match ($type) {
            MappedType::ABS            => \abs($value),
            MappedType::SQUARE         => $value * $value,
            MappedType::CUBE           => $value * $value * $value,
            MappedType::HALF_NEGATIVE  => $value > 0.0 ? $value : $value * 0.5,
            MappedType::QUARTER_NEGATIVE => $value > 0.0 ? $value : $value * 0.25,
            MappedType::INVERT         => $value == 0.0 ? INF : 1.0 / $value,
            MappedType::SQUEEZE        => (($clamped = \max(-1.0, \min(1.0, $value))) / 2.0 - $clamped * $clamped * $clamped / 24.0),
        };
    }

    public function input(): DensityFunction { return $this->input; }

    public function transform(float $inputValue): float {
        return self::transformType($this->type, $inputValue);
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