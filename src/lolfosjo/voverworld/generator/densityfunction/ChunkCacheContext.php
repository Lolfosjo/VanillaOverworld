<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface ChunkCacheContext extends FunctionContext {
    public function densityChunkCache(): ChunkCache;
}