<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class YCoordinate implements DensityFunction
{
    public function compute(FunctionContext $context): float
    {
        return (float) $context->blockY();
    }

    public function fillArray(array &$output, ContextProvider $contextProvider): void
    {
        $contextProvider->fillAllDirectly($output, $this);
    }

    public function minValue(): float
    {
        return -INF;
    }

    public function maxValue(): float
    {
        return INF;
    }
}