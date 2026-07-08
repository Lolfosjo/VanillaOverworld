<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface ContextProvider {
    public function forIndex(int $index): FunctionContext;
    public function fillAllDirectly(array &$output, DensityFunction $function): void;
}