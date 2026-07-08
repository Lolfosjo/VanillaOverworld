<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

abstract class AbstractDensityFunction implements DensityFunction {
    public function clamp(float $min, float $max): DensityFunction {
        return new Clamp($this, $min, $max);
    }
    public function abs(): DensityFunction {
        return DensityCommon::map($this, MappedType::ABS);
    }
    public function square(): DensityFunction {
        return DensityCommon::map($this, MappedType::SQUARE);
    }
    public function cube(): DensityFunction {
        return DensityCommon::map($this, MappedType::CUBE);
    }
    public function halfNegative(): DensityFunction {
        return DensityCommon::map($this, MappedType::HALF_NEGATIVE);
    }
    public function quarterNegative(): DensityFunction {
        return DensityCommon::map($this, MappedType::QUARTER_NEGATIVE);
    }
    public function invert(): DensityFunction {
        return DensityCommon::map($this, MappedType::INVERT);
    }
    public function squeeze(): DensityFunction {
        return DensityCommon::map($this, MappedType::SQUEEZE);
    }
}