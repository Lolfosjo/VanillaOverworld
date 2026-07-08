<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\material;

use lolfosjo\voverworld\generator\densityfunction\FunctionContext;
use pocketmine\block\Block;

final class MultiMaterial implements MaterialFiller
{
    /** @var MaterialFiller[] */
    private array $materials;

    /**
     * @param MaterialFiller[] $materials
     */
    public function __construct(array $materials)
    {
        $this->materials = $materials;
    }

    public function calculate(FunctionContext $context): ?Block
    {
        foreach ($this->materials as $rule) {
            if ($rule === null) {
                continue;
            }
            $block = $rule->calculate($context);
            if ($block !== null) {
                return $block;
            }
        }
        return null;
    }
}