<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class ChunkCache {
    private \SplObjectStorage $states;
    public function __construct() {
        $this->states = new \SplObjectStorage();
    }
    public function getOrCreate(Marker $marker, callable $supplier): mixed {
        if ($this->states->contains($marker)) {
            return $this->states[$marker];
        }
        $value = $supplier();
        $this->states[$marker] = $value;
        return $value;
    }
    public function clear(): void {
        $this->states->removeAll($this->states);
    }
}