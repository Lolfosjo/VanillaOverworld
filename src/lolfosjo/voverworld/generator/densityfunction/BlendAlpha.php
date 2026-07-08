<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class BlendAlpha extends AbstractDensityFunction {
    private static ?self $instance = null;
    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }
    private function __construct() {}
    public function compute(FunctionContext $context): float { return 1.0; }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $output = array_fill(0, count($output), 1.0);
    }
    public function minValue(): float { return 1.0; }
    public function maxValue(): float { return 1.0; }
}