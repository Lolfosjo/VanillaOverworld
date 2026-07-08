<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

use pocketmine\world\format\Chunk;

final class DensityCommon {
    private static array $chunkCaches = [];

    private function __construct() {}

    public static function chunkCache(Chunk $chunk): ChunkCache {
        $hash = spl_object_hash($chunk);
        if (!isset(self::$chunkCaches[$hash])) {
            self::$chunkCaches[$hash] = new ChunkCache();
        }
        return self::$chunkCaches[$hash];
    }

    public static function releaseChunkCache(Chunk $chunk): void {
        $hash = spl_object_hash($chunk);
        unset(self::$chunkCaches[$hash]);
    }

    public static function interpolated(DensityFunction $function): DensityFunction {
        return new InterpolatedMarker($function);
    }

    public static function flatCache(DensityFunction $function): DensityFunction {
        return new FlatCacheMarker($function);
    }

    public static function cache2d(DensityFunction $function): DensityFunction {
        return new Cache2DMarker($function);
    }

    public static function cacheOnce(DensityFunction $function): DensityFunction {
        return new CacheOnceMarker($function);
    }

    public static function cacheAllInCell(DensityFunction $function): DensityFunction {
        return new CacheAllInCellMarker($function);
    }

    public static function noise(NoiseHolder $noise, float $xzScale = 1.0, float $yScale = 1.0): DensityFunction {
        return new Noise($noise, $xzScale, $yScale);
    }

    public static function mappedNoise(NoiseHolder $noise, float $min, float $max, float $xzScale = 1.0, float $yScale = 1.0): DensityFunction {
        return self::mapFromUnitTo(self::noise($noise, $xzScale, $yScale), $min, $max);
    }

    private static function mapFromUnitTo(DensityFunction $input, float $min, float $max): DensityFunction {
        $mid = ($min + $max) * 0.5;
        $scale = ($max - $min) * 0.5;
        return self::add(self::constant($mid), self::mul(self::constant($scale), $input));
    }

    public static function weirdScaledSampler(DensityFunction $input, NoiseHolder $noise, RarityValueMapper $mapper): DensityFunction {
        return new WeirdScaledSampler($input, $noise, $mapper);
    }

    public static function shiftA(NoiseHolder $noise): DensityFunction {
        return new ShiftA($noise);
    }

    public static function shiftB(NoiseHolder $noise): DensityFunction {
        return new ShiftB($noise);
    }

    public static function shift(NoiseHolder $noise): DensityFunction {
        return new Shift($noise);
    }

    public static function blendAlpha(): DensityFunction {
        return BlendAlpha::getInstance();
    }

    public static function blendOffset(): DensityFunction {
        return BlendOffset::getInstance();
    }

    public static function blendDensity(DensityFunction $input): DensityFunction {
        return $input;
    }

    public static function rangeChoice(
        DensityFunction $input,
        float $minInclusive,
        float $maxExclusive,
        DensityFunction $whenInRange,
        DensityFunction $whenOutOfRange
    ): DensityFunction {
        return new RangeChoice($input, $minInclusive, $maxExclusive, $whenInRange, $whenOutOfRange);
    }

    public static function add(DensityFunction $f1, DensityFunction $f2): DensityFunction {
        return Ap2Factory::create(TwoArgumentType::ADD, $f1, $f2);
    }

    public static function mul(DensityFunction $f1, DensityFunction $f2): DensityFunction {
        return Ap2Factory::create(TwoArgumentType::MUL, $f1, $f2);
    }

    public static function min(DensityFunction $f1, DensityFunction $f2): DensityFunction {
        return Ap2Factory::create(TwoArgumentType::MIN, $f1, $f2);
    }

    public static function max(DensityFunction $f1, DensityFunction $f2): DensityFunction {
        return Ap2Factory::create(TwoArgumentType::MAX, $f1, $f2);
    }

    public static function zero(): DensityFunction {
        return new Constant(0.0);
    }

    public static function constant(float $value): DensityFunction {
        return new Constant($value);
    }

    public static function yClampedGradient(int $fromY, int $toY, float $fromValue, float $toValue): DensityFunction {
        return new YClampedGradient($fromY, $toY, $fromValue, $toValue);
    }

    public static function map(DensityFunction $function, MappedType $type): DensityFunction {
        return Mapped::create($type, $function);
    }

    public static function lerp(DensityFunction $factor, float $first, DensityFunction $second): DensityFunction {
        return self::add(self::mul($factor, self::add($second, self::constant(-$first))), self::constant($first));
    }

    public static function spline(CubicSpline $spline): DensityFunction {
        return new Spline($spline);
    }

    public static function splineFromPoints(DensityFunction $coordinate, SplinePoint ...$points): DensityFunction {
        $builder = CubicSpline::builder(function($input) use ($coordinate) {
            if ($input instanceof SplineContextPoint) {
                return $coordinate->compute($input->context());
            }
            return 0.0;
        });
        foreach ($points as $p) {
            if ($p->value instanceof DensityFunction) {
                $builder->addPointValue($p->location, new SplineCoordinate($p->value), $p->derivative);
            } else {
                $builder->addPoint($p->location, (float)$p->value, $p->derivative);
            }
        }
        return new Spline($builder->build());
    }

    public static function splinePoint(float $location, $value, float $derivative): SplinePoint {
        return new SplinePoint($location, $value, $derivative);
    }
}

final class Ap2Factory {
    public static function create(TwoArgumentType $type, DensityFunction $arg1, DensityFunction $arg2): DensityFunction {
        $min1 = $arg1->minValue();
        $min2 = $arg2->minValue();
        $max1 = $arg1->maxValue();
        $max2 = $arg2->maxValue();

        $minValue = match ($type) {
            TwoArgumentType::ADD => $min1 + $min2,
            TwoArgumentType::MUL => ($min1 > 0.0 && $min2 > 0.0) ? $min1 * $min2 : (($max1 < 0.0 && $max2 < 0.0) ? $max1 * $max2 : \min($min1 * $max2, $max1 * $min2)),
            TwoArgumentType::MIN => \min($min1, $min2),
            TwoArgumentType::MAX => \max($min1, $min2),
        };

        $maxValue = match ($type) {
            TwoArgumentType::ADD => $max1 + $max2,
            TwoArgumentType::MUL => ($min1 > 0.0 && $min2 > 0.0) ? $max1 * $max2 : (($max1 < 0.0 && $max2 < 0.0) ? $min1 * $min2 : \max($min1 * $min2, $max1 * $max2)),
            TwoArgumentType::MIN => \min($max1, $max2),
            TwoArgumentType::MAX => \max($max1, $max2),
        };

        if (($type === TwoArgumentType::ADD || $type === TwoArgumentType::MUL) && $arg1 instanceof Constant) {
            return new MulOrAdd(
                $type === TwoArgumentType::ADD ? MulOrAddType::ADD : MulOrAddType::MUL,
                $arg2,
                $minValue,
                $maxValue,
                $arg1->compute(new SinglePointContext(0,0,0))
            );
        }
        if (($type === TwoArgumentType::ADD || $type === TwoArgumentType::MUL) && $arg2 instanceof Constant) {
            return new MulOrAdd(
                $type === TwoArgumentType::ADD ? MulOrAddType::ADD : MulOrAddType::MUL,
                $arg1,
                $minValue,
                $maxValue,
                $arg2->compute(new SinglePointContext(0,0,0))
            );
        }

        return new Ap2($type, $arg1, $arg2, $minValue, $maxValue);
    }
}