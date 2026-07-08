<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

final class DensityDepth
{
    private const FROM_Y = -64;
    private const TO_Y = 320;
    private const FROM_VALUE = 1.5;
    private const TO_VALUE = -1.5;

    private function __construct() {}

    public static function overworldDepth(DensityFunction $offset): DensityFunction
    {
        return DensityCommon::add(
            DensityCommon::yClampedGradient(self::FROM_Y, self::TO_Y, self::FROM_VALUE, self::TO_VALUE),
            $offset
        );
    }
}