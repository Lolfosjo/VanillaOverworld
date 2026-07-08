<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class FlatCacheMarker extends Marker {
    private const CHUNK_SIZE = 16;

    // Single cached chunk — only one chunk's data is held at a time.
    private ?int $cachedChunkX = null;
    private ?int $cachedChunkZ = null;
    private array $values      = [];   // float[256]
    private array $filledBits  = [];   // int[4]  (256 bits)

    public function __construct(DensityFunction $wrapped) {
        parent::__construct(MarkerType::FLAT_CACHE, $wrapped);
    }

    /**
     * Call this at the start of every generateChunk() to invalidate stale data.
     */
    public function invalidate(): void {
        $this->cachedChunkX = null;
        $this->cachedChunkZ = null;
        $this->values       = [];
        $this->filledBits   = [];
    }

    public function compute(FunctionContext $context): float {
        $blockX = $context->blockX();
        $blockZ = $context->blockZ();
        $chunkX = $blockX >> 4;
        $chunkZ = $blockZ >> 4;

        // If we've moved to a different chunk, wipe the single-cell cache.
        if ($chunkX !== $this->cachedChunkX || $chunkZ !== $this->cachedChunkZ) {
            $this->cachedChunkX = $chunkX;
            $this->cachedChunkZ = $chunkZ;
            $count              = self::CHUNK_SIZE * self::CHUNK_SIZE; // 256
            $this->values       = array_fill(0, $count, 0.0);
            $this->filledBits   = array_fill(0, ($count + 63) >> 6, 0); // 4 ints
        }

        $firstBlockX = $chunkX << 4;
        $firstBlockZ = $chunkZ << 4;
        $localX      = $blockX - $firstBlockX;
        $localZ      = $blockZ - $firstBlockZ;

        if ($localX >= 0 && $localX < self::CHUNK_SIZE
            && $localZ >= 0 && $localZ < self::CHUNK_SIZE
        ) {
            $index = $localX + $localZ * self::CHUNK_SIZE;
            $word  = $index >> 6;
            $bit   = 1 << ($index & 63);

            if (($this->filledBits[$word] & $bit) !== 0) {
                return $this->values[$index];
            }

            $ctx = new MutableFunctionContext();
            $val = $this->wrapped->compute($ctx->set($blockX, 0, $blockZ));
            $this->values[$index]      = $val;
            $this->filledBits[$word]  |= $bit;
            return $val;
        }

        // Out-of-bounds for this chunk — compute without caching.
        return $this->wrapped->compute($context);
    }
}

class FlatCacheCell {
    public int $firstBlockX;
    public int $firstBlockZ;
    public array $values;
    public array $filledBits;
    public function __construct(int $firstBlockX, int $firstBlockZ, array $values, array $filledBits) {
        $this->firstBlockX = $firstBlockX;
        $this->firstBlockZ = $firstBlockZ;
        $this->values = $values;
        $this->filledBits = $filledBits;
    }
}