<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class CacheOnceMarker extends Marker {
    private ?int $lastBlockX = null;
    private ?int $lastBlockY = null;
    private ?int $lastBlockZ = null;
    private ?float $lastValue = null;

    public function __construct(DensityFunction $wrapped) {
        parent::__construct(MarkerType::CACHE_ONCE, $wrapped);
    }

    public function compute(FunctionContext $context): float {
        $bx = $context->blockX();
        $by = $context->blockY();
        $bz = $context->blockZ();
        if ($this->lastBlockX === $bx && $this->lastBlockY === $by && $this->lastBlockZ === $bz) {
            return $this->lastValue;
        }
        $this->lastBlockX = $bx;
        $this->lastBlockY = $by;
        $this->lastBlockZ = $bz;
        $this->lastValue = $this->wrapped->compute($context);
        return $this->lastValue;
    }
}