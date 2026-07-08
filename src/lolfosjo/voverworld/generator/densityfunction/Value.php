<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface Value
{
    public function apply(mixed $input): float;

    public function minValue(): float;

    public function maxValue(): float;
}