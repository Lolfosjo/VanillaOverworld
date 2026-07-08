<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class Point
{
    public function __construct(
        public readonly float $location,
        public readonly Value $value,
        public readonly float $derivative
    ) {
    }
}