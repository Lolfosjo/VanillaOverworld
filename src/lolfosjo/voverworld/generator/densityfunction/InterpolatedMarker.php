<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class InterpolatedMarker extends Marker {
    private const CELL_SIZE_XZ = 4;
    private const CELL_SIZE_Y = 8;
    private const CELL_XZ_MASK = 3;
    private const CELL_Y_MASK = 7;
    private const CELL_VALUE_COUNT = 4 * 8 * 4;
    private const INV_CELL_SIZE_XZ = 1.0 / 4;
    private const INV_CELL_SIZE_Y = 1.0 / 8;

    // Maximum number of cached cells – prevents memory overflows

    private const MAX_CACHE_SIZE = 64;

    private array $cache = [];          // [cellKey => array of 128 floats]
    private array $cacheOrder = [];     // FIFO queue of keys for eviction

    public function __construct(DensityFunction $wrapped) {
        parent::__construct(MarkerType::INTERPOLATED, $wrapped);
    }

    public function compute(FunctionContext $context): float {
        $blockX = $context->blockX();
        $blockY = $context->blockY();
        $blockZ = $context->blockZ();
        $cellX = ($blockX >> 2) << 2;
        $cellY = ($blockY >> 3) << 3;
        $cellZ = ($blockZ >> 2) << 2;
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

        $values = $this->fillCell($cellX, $cellY, $cellZ, $sourceContext);
        $this->cache[$key] = $values;
        $this->cacheOrder[] = $key;

        // Evict the oldest entry when the limit is exceeded
        if (count($this->cacheOrder) > self::MAX_CACHE_SIZE) {
            $oldestKey = array_shift($this->cacheOrder);
            unset($this->cache[$oldestKey]);
        }

        return $values;
    }

    private function fillCell(int $cellX, int $cellY, int $cellZ, FunctionContext $sourceContext): array {
        $nextX = $cellX + self::CELL_SIZE_XZ;
        $nextY = $cellY + self::CELL_SIZE_Y;
        $nextZ = $cellZ + self::CELL_SIZE_XZ;

        $ctx = $sourceContext instanceof ChunkCacheContext
            ? new CellFunctionContext($sourceContext->densityChunkCache())
            : new MutableFunctionContext();

        $d000 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($cellX, $cellY, $cellZ) : $ctx->set($cellX, $cellY, $cellZ));
        $d100 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($nextX, $cellY, $cellZ) : $ctx->set($nextX, $cellY, $cellZ));
        $d010 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($cellX, $nextY, $cellZ) : $ctx->set($cellX, $nextY, $cellZ));
        $d110 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($nextX, $nextY, $cellZ) : $ctx->set($nextX, $nextY, $cellZ));
        $d001 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($cellX, $cellY, $nextZ) : $ctx->set($cellX, $cellY, $nextZ));
        $d101 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($nextX, $cellY, $nextZ) : $ctx->set($nextX, $cellY, $nextZ));
        $d011 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($cellX, $nextY, $nextZ) : $ctx->set($cellX, $nextY, $nextZ));
        $d111 = $this->wrapped->compute($ctx instanceof MutableFunctionContext ? $ctx->set($nextX, $nextY, $nextZ) : $ctx->set($nextX, $nextY, $nextZ));

        $c000 = $d000;
        $c100 = $d100 - $d000;
        $c010 = $d010 - $d000;
        $c001 = $d001 - $d000;
        $c110 = $d110 - $d100 - $d010 + $d000;
        $c101 = $d101 - $d100 - $d001 + $d000;
        $c011 = $d011 - $d010 - $d001 + $d000;
        $c111 = $d111 - $d110 - $d101 - $d011 + $d100 + $d010 + $d001 - $d000;

        $values = array_fill(0, self::CELL_VALUE_COUNT, 0.0);
        $idx = 0;
        for ($localY = 0; $localY < self::CELL_SIZE_Y; $localY++) {
            $yAlpha = $localY * self::INV_CELL_SIZE_Y;
            for ($localZ = 0; $localZ < self::CELL_SIZE_XZ; $localZ++) {
                $zAlpha = $localZ * self::INV_CELL_SIZE_XZ;
                $yz = $yAlpha * $zAlpha;
                $zTerm = $zAlpha * ($c001 + $yAlpha * $c011);
                for ($localX = 0; $localX < self::CELL_SIZE_XZ; $localX++) {
                    $xAlpha = $localX * self::INV_CELL_SIZE_XZ;
                    $values[$idx++] = $c000
                        + $xAlpha * ($c100 + $yAlpha * $c110 + $zAlpha * $c101 + $yz * $c111)
                        + $yAlpha * $c010
                        + $zTerm;
                }
            }
        }
        return $values;
    }
}