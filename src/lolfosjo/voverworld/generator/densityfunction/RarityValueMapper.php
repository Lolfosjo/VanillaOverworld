<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class RarityValueMapper {
    public const TYPE1 = 0;
    public const TYPE2 = 1;

    private int $type;

    public function __construct(int $type) {
        $this->type = $type;
    }

    public function apply(float $value): float {
        if ($this->type === self::TYPE1) {
            if ($value < -0.75) {
                return 0.5;
            }

            if ($value < -0.5) {
                return 0.75;
            }

            if ($value < 0.5) {
                return 1.0;
            }

            if ($value < 0.75) {
                return 2.0;
            }

            return 3.0;
        }

        if ($value < -0.5) {
            return 0.75;
        }

        if ($value < 0.0) {
            return 1.0;
        }

        if ($value < 0.5) {
            return 1.5;
        }

        return 2.0;
    }

    public function maxRarity(): float {
        return $this->type === self::TYPE1 ? 3.0 : 2.0;
    }
}