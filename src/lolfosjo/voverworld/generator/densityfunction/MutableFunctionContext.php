<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class MutableFunctionContext implements FunctionContext {
    private int $blockX;
    private int $blockY;
    private int $blockZ;
    public function set(int $blockX, int $blockY, int $blockZ): self {
        $this->blockX = $blockX;
        $this->blockY = $blockY;
        $this->blockZ = $blockZ;
        return $this;
    }
    public function blockX(): int { return $this->blockX; }
    public function blockY(): int { return $this->blockY; }
    public function blockZ(): int { return $this->blockZ; }
}