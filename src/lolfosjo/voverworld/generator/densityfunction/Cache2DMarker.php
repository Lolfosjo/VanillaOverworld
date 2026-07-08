<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class Cache2DMarker extends Marker {
    private ?int $lastPos2d = null;
    private ?float $lastValue = null;

    public function __construct(DensityFunction $wrapped) {
        parent::__construct(MarkerType::CACHE_2D, $wrapped);
    }

    public function compute(FunctionContext $context): float {
        $blockX = $context->blockX();
        $blockZ = $context->blockZ();
        $pos = (($blockX & 0xFFFFFFFF) << 32) | ($blockZ & 0xFFFFFFFF);
        if ($this->lastPos2d === $pos) {
            return $this->lastValue;
        }
        $this->lastPos2d = $pos;
        $this->lastValue = $this->wrapped->compute($context);
        return $this->lastValue;
    }
}