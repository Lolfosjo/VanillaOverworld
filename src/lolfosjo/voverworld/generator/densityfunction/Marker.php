<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

abstract class Marker extends AbstractDensityFunction {
    protected DensityFunction $wrapped;
    protected MarkerType $type;
    public function __construct(MarkerType $type, DensityFunction $wrapped) {
        $this->type = $type;
        $this->wrapped = $wrapped;
    }
    public function minValue(): float { return $this->wrapped->minValue(); }
    public function maxValue(): float { return $this->wrapped->maxValue(); }
    
    // Standard implementation for fillArray – calls compute for each index

    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        $contextProvider->fillAllDirectly($output, $this);
    }

    protected function state(FunctionContext $context, callable $stateFactory): mixed {
        if ($context instanceof ChunkCacheContext) {
            return $context->densityChunkCache()->getOrCreate($this, $stateFactory);
        }
        return $stateFactory();
    }
}

enum MarkerType {
    case INTERPOLATED;
    case FLAT_CACHE;
    case CACHE_2D;
    case CACHE_ONCE;
    case CACHE_ALL_IN_CELL;
}