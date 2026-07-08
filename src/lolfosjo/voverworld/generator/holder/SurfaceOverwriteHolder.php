<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\generator\noise\minecraft\noise\NormalNoise;
use lolfosjo\voverworld\util\math\RandomSourceProvider;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;

class SurfaceOverwriteHolder extends RandomizedObjectHolder {
    private NormalNoise $surfaceNoise;
    private NormalNoise $swampNoise;
    private NormalNoise $clayBandsOffsetNoise;
    private NormalNoise $badlandsPillarNoise;
    private NormalNoise $badlandsPillarRoofNoise;
    private NormalNoise $badlandsSurfaceNoise;
    private NormalNoise $icebergPillarNoise;
    private NormalNoise $icebergPillarRoofNoise;
    private NormalNoise $icebergSurfaceNoise;
    /** @var Block[] */
    private array $clayBandsCache;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        parent::__construct($randomSourceProvider);
        $this->surfaceNoise = new NormalNoise($randomSourceProvider->identical(), -6, [1, 1, 1]);
        $this->swampNoise = new NormalNoise($randomSourceProvider->identical(), -2, [1]);
        $this->clayBandsOffsetNoise = new NormalNoise($randomSourceProvider->identical(), -8, [1]);
        $this->badlandsPillarNoise = new NormalNoise($randomSourceProvider->identical(), -2, [1, 1, 1]);
        $this->badlandsPillarRoofNoise = new NormalNoise($randomSourceProvider->identical(), -8, [1]);
        $this->badlandsSurfaceNoise = new NormalNoise($randomSourceProvider->identical(), -6, [1, 1, 1]);
        $this->icebergPillarNoise = new NormalNoise($randomSourceProvider->identical(), -6, [1, 1, 1, 1]);
        $this->icebergPillarRoofNoise = new NormalNoise($randomSourceProvider->identical(), -3, [1]);
        $this->icebergSurfaceNoise = new NormalNoise($randomSourceProvider->identical(), -6, [1, 1, 1]);
        $this->clayBandsCache = array_fill(0, 192, null);
    }

    public function getSurfaceNoise(): NormalNoise { return $this->surfaceNoise; }
    public function getSwampNoise(): NormalNoise { return $this->swampNoise; }
    public function getClayBandsOffsetNoise(): NormalNoise { return $this->clayBandsOffsetNoise; }
    public function getBadlandsPillarNoise(): NormalNoise { return $this->badlandsPillarNoise; }
    public function getBadlandsPillarRoofNoise(): NormalNoise { return $this->badlandsPillarRoofNoise; }
    public function getBadlandsSurfaceNoise(): NormalNoise { return $this->badlandsSurfaceNoise; }
    public function getIcebergPillarNoise(): NormalNoise { return $this->icebergPillarNoise; }
    public function getIcebergPillarRoofNoise(): NormalNoise { return $this->icebergPillarRoofNoise; }
    public function getIcebergSurfaceNoise(): NormalNoise { return $this->icebergSurfaceNoise; }
    /** @return Block[] */
    public function getClayBandsCache(): array { return $this->clayBandsCache; }

    public function initializeClayBands(int $seed): void {
        if ($this->clayBandsCache[0] !== null) {
            return; 
        }

        $random = new Random($seed ^ crc32("clay_bands"));
        $bands = array_fill(0, 192, VanillaBlocks::STAINED_CLAY());

        for ($i = 0; $i < count($bands); $i++) {
            $i += $random->nextBoundedInt(5) + 1;
            if ($i < count($bands)) {
                $bands[$i] = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::ORANGE);
            }
        }

        $this->makeBands($random, $bands, 1, VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::YELLOW));
        $this->makeBands($random, $bands, 2, VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::BROWN));
        $this->makeBands($random, $bands, 1, VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::RED));

        $whiteBandCount = $random->nextBoundedInt(15 - 9 + 1) + 9; // [9,15]
        $count = 0;
        for ($start = 0; $count < $whiteBandCount && $start < count($bands); $start += $random->nextBoundedInt(16) + 4) {
            $bands[$start] = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::WHITE);
            if ($start - 1 > 0 && $random->nextBoolean()) {
                $bands[$start - 1] = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::LIGHT_GRAY);
            }
            if ($start + 1 < count($bands) && $random->nextBoolean()) {
                $bands[$start + 1] = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::LIGHT_GRAY);
            }
            $count++;
        }

        $this->clayBandsCache = $bands;
    }

    private function makeBands(Random $random, array &$bands, int $baseWidth, Block $state): void {
        $bandCount = $random->nextBoundedInt(15 - 6 + 1) + 6; // [6,15]
        for ($i = 0; $i < $bandCount; $i++) {
            $width = $baseWidth + $random->nextBoundedInt(3);
            $start = $random->nextBoundedInt(count($bands) - 1);
            for ($p = 0; $start + $p < count($bands) && $p < $width; $p++) {
                $bands[$start + $p] = $state;
            }
        }
    }
}