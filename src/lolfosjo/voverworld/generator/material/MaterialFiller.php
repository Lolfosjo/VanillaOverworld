<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\material;

use lolfosjo\voverworld\generator\densityfunction\FunctionContext;
use pocketmine\block\Block;

interface MaterialFiller
{
    public function calculate(FunctionContext $context): ?Block;
}