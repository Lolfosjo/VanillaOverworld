<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\generator\noise\minecraft\noise\NormalNoise;
use lolfosjo\voverworld\util\math\RandomSourceProvider;

class SurfaceHolder extends RandomizedObjectHolder {
    private NormalNoise $noise;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        parent::__construct($randomSourceProvider);
        $this->noise = new NormalNoise($randomSourceProvider->identical(), -6, [1, 1, 1]);
    }

    public function getNoise(): NormalNoise { return $this->noise; }
}