<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class CacheAllInCellMarker extends Marker {
    private const CELL_SIZE_XZ = 4;
    private const CELL_SIZE_Y = 8;
    private const CELL_XZ_MASK = 3;
    private const CELL_Y_MASK = 7;
    private const CELL_VALUE_COUNT = 4 * 8 * 4;

    // Maximum number of cached cells – prevents memory overflows

    private const MAX_CACHE_SIZE = 64;

    private array $cache = [];          // [cellKey => array of 128 floats]
    private array $cacheOrder = [];     // FIFO queue of keys for eviction

    public function __construct(DensityFunction $wrapped) {
        parent::__construct(MarkerType::CACHE_ALL_IN_CELL, $wrapped);
    }

    public function compute(FunctionContext $context): float {
        $blockX = $context->blockX();
        $blockY = $context->blockY();
        $blockZ = $context->blockZ();
        $cellX = $blockX >> 2;
        $cellY = $blockY >> 3;
        $cellZ = $blockZ >> 2;
        $values = $this->getOrCreateCell($cellX, $cellY, $cellZ, $context);
        $index = (($blockY & self::CELL_Y_MASK) << 4)
               | (($blockZ & self::CELL_XZ_MASK) << 2)
               | ($blockX & self::CELL_XZ_MASK);
        return $values[$index];
    }

    private function getOrCreateCell(int $cellX, int $cellY, int $cellZ, FunctionContext $sourceContext): array {
        $key = ((($cellX & 0x1FFFFF) << 42) | (($cellY & 0x1FFFFF) << 21) | ($cellZ & 0x1FFFFF));
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $values = $this->fillCell($cellX << 2, $cellY << 3, $cellZ << 2, $sourceContext);
        $this->cache[$key] = $values;
        $this->cacheOrder[] = $key;

        // Evict the oldest entry when the limit is exceeded
        if (count($this->cacheOrder) > self::MAX_CACHE_SIZE) {
            $oldestKey = array_shift($this->cacheOrder);
            unset($this->cache[$oldestKey]);
        }

        return $values;
    }

    private function fillCell(int $startX, int $startY, int $startZ, FunctionContext $sourceContext): array {
        $values = array_fill(0, self::CELL_VALUE_COUNT, 0.0);
        $ctx = $sourceContext instanceof ChunkCacheContext
            ? new CellFunctionContext($sourceContext->densityChunkCache())
            : new MutableFunctionContext();
        $idx = 0;
        for ($localY = 0; $localY < self::CELL_SIZE_Y; $localY++) {
            $y = $startY + $localY;
            for ($localZ = 0; $localZ < self::CELL_SIZE_XZ; $localZ++) {
                $z = $startZ + $localZ;
                for ($localX = 0; $localX < self::CELL_SIZE_XZ; $localX++) {
                    $x = $startX + $localX;
                    $values[$idx++] = $this->wrapped->compute(
                        $ctx instanceof MutableFunctionContext ? $ctx->set($x, $y, $z) : $ctx->set($x, $y, $z)
                    );
                }
            }
        }
        return $values;
    }
}