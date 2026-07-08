<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class ConstantValue implements Value
{
    public function __construct(
        private readonly float $value
    ) {
    }

    public function apply(mixed $input): float
    {
        return $this->value;
    }

    public function minValue(): float
    {
        return $this->value;
    }

    public function maxValue(): float
    {
        return $this->value;
    }
}