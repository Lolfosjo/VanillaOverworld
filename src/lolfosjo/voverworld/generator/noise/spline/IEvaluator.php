<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\spline;

interface IEvaluator
{
    public function evaluate(array $parameters): float;
}