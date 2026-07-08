<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class SinglePointContext implements FunctionContext {
    public function __construct(
        private int $blockX,
        private int $blockY,
        private int $blockZ
    ) {}
    public function blockX(): int { return $this->blockX; }
    public function blockY(): int { return $this->blockY; }
    public function blockZ(): int { return $this->blockZ; }
}