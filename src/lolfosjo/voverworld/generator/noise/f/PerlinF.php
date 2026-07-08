<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\noise\f;

use lolfosjo\voverworld\util\math\RandomSourceProvider;

class PerlinF extends NoiseF
{
    public function __construct(RandomSourceProvider $random, float $octaves, float $persistence, float $expansion = 1.0)
    {
        $this->octaves = $octaves;
        $this->persistence = $persistence;
        $this->expansion = $expansion;
        $this->offsetX = $random->nextFloat() * 256.0;
        $this->offsetY = $random->nextFloat() * 256.0;
        $this->offsetZ = $random->nextFloat() * 256.0;
        $this->perm = array_fill(0, 512, 0);

        // 256 random values (0–255)
        for ($i = 0; $i < 256; ++$i) {
            $this->perm[$i] = $random->nextInt(256); // nextInt(256) -> 0..255
        }

        // Shuffle (like in Java)
        for ($i = 0; $i < 256; ++$i) {
            $pos = $random->nextInt(255 - $i) + $i; // 0..(254-i)+i => 0..254
            $old = $this->perm[$i];
            $this->perm[$i] = $this->perm[$pos];
            $this->perm[$pos] = $old;
            $this->perm[$i + 256] = $this->perm[$i];
        }
    }

    public function getNoise2D(float $x, float $z): float
    {
        return $this->getNoise3D($x, $z, 0.0);
    }

    public function getNoise3D(float $x, float $y, float $z): float
    {
        $x += $this->offsetX;
        $y += $this->offsetY;
        $z += $this->offsetZ;

        $floorX = (int)$x;
        $floorY = (int)$y;
        $floorZ = (int)$z;

        $X = $floorX & 0xFF;
        $Y = $floorY & 0xFF;
        $Z = $floorZ & 0xFF;

        $x -= $floorX;
        $y -= $floorY;
        $z -= $floorZ;

        $fX = $x * $x * $x * ($x * ($x * 6.0 - 15.0) + 10.0);
        $fY = $y * $y * $y * ($y * ($y * 6.0 - 15.0) + 10.0);
        $fZ = $z * $z * $z * ($z * ($z * 6.0 - 15.0) + 10.0);

        $A = $this->perm[$X] + $Y;
        $B = $this->perm[$X + 1] + $Y;

        $AA = $this->perm[$A] + $Z;
        $AB = $this->perm[$A + 1] + $Z;
        $BA = $this->perm[$B] + $Z;
        $BB = $this->perm[$B + 1] + $Z;

        $AA1 = self::grad($this->perm[$AA], $x, $y, $z);
        $BA1 = self::grad($this->perm[$BA], $x - 1, $y, $z);
        $AB1 = self::grad($this->perm[$AB], $x, $y - 1, $z);
        $BB1 = self::grad($this->perm[$BB], $x - 1, $y - 1, $z);
        $AA2 = self::grad($this->perm[$AA + 1], $x, $y, $z - 1);
        $BA2 = self::grad($this->perm[$BA + 1], $x - 1, $y, $z - 1);
        $AB2 = self::grad($this->perm[$AB + 1], $x, $y - 1, $z - 1);
        $BB2 = self::grad($this->perm[$BB + 1], $x - 1, $y - 1, $z - 1);

        $xLerp11 = $AA1 + $fX * ($BA1 - $AA1);
        $zLerp1 = $xLerp11 + $fY * ($AB1 + $fX * ($BB1 - $AB1) - $xLerp11);

        $xLerp21 = $AA2 + $fX * ($BA2 - $AA2);

        return $zLerp1 + $fZ * ($xLerp21 + $fY * ($AB2 + $fX * ($BB2 - $AB2) - $xLerp21) - $zLerp1);
    }
}