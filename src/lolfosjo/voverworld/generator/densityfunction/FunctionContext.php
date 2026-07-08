<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

interface FunctionContext {
    public function blockX(): int;
    public function blockY(): int;
    public function blockZ(): int;
}