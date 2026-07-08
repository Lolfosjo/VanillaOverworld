<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class CellFunctionContext implements ChunkCacheContext {
    private int $worldX;
    private int $worldY;
    private int $worldZ;
    private ChunkCache $chunkCache;
    public function __construct(ChunkCache $chunkCache) {
        $this->chunkCache = $chunkCache;
    }
    public function set(int $worldX, int $worldY, int $worldZ): self {
        $this->worldX = $worldX;
        $this->worldY = $worldY;
        $this->worldZ = $worldZ;
        return $this;
    }
    public function blockX(): int { return $this->worldX; }
    public function blockY(): int { return $this->worldY; }
    public function blockZ(): int { return $this->worldZ; }
    public function densityChunkCache(): ChunkCache { return $this->chunkCache; }
}