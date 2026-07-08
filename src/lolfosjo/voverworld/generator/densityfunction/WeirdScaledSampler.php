<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

class WeirdScaledSampler extends AbstractDensityFunction implements TransformerWithContext {
    private DensityFunction $input;
    private NoiseHolder $noise;
    private RarityValueMapper $rarityValueMapper;
    public function __construct(DensityFunction $input, NoiseHolder $noise, RarityValueMapper $rarityValueMapper) {
        $this->input = $input;
        $this->noise = $noise;
        $this->rarityValueMapper = $rarityValueMapper;
    }
    public function input(): DensityFunction { return $this->input; }
    public function transform(FunctionContext $context, float $inputValue): float {
        $rarity = $this->rarityValueMapper->apply($inputValue);
        return $rarity * \abs($this->noise->getValue(
            $context->blockX() / $rarity,
            $context->blockY() / $rarity,
            $context->blockZ() / $rarity
        ));
    }
    public function compute(FunctionContext $context): float {
        return $this->transform($context, $this->input->compute($context));
    }
    public function fillArray(array &$output, ContextProvider $contextProvider): void {
        for ($i = 0; $i < count($output); $i++) {
            $ctx = $contextProvider->forIndex($i);
            $output[$i] = $this->transform($ctx, $this->input->compute($ctx));
        }
    }
    public function minValue(): float { return 0.0; }
    public function maxValue(): float { return $this->rarityValueMapper->maxRarity() * $this->noise->maxValue(); }
}