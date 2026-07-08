<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\util\math\RandomSourceProvider;

class RandomizedObjectHolder extends ObjectHolder {
    protected RandomSourceProvider $randomSourceProvider;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        $this->randomSourceProvider = $randomSourceProvider;
    }

    public function getRandomSourceProvider(): RandomSourceProvider {
        return $this->randomSourceProvider;
    }
}