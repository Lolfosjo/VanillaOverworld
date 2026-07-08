<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\generator\noise\f\SimplexF;
use lolfosjo\voverworld\generator\noise\minecraft\simplex\SimplexNoise;
use lolfosjo\voverworld\util\math\RandomSourceProvider;

class FeatureHolder extends RandomizedObjectHolder {
    private SimplexF $randomClayWithDripleavesSnapToFloor;
    private SimplexF $dripstoneCluster;
    private SimplexF $mossPatchSnapToFloor;
    private SimplexF $mossSnapToCeiling;
    private SimplexF $sculkPatch;
    private SimplexNoise $kelp;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        parent::__construct($randomSourceProvider);
        $this->randomClayWithDripleavesSnapToFloor = new SimplexF($randomSourceProvider->identical(), 1.0, 2/4, 1/15);
        $this->dripstoneCluster = new SimplexF($randomSourceProvider->identical(), 30.0, 1/99, 1/15);
        $this->mossPatchSnapToFloor = new SimplexF($randomSourceProvider->identical(), 2.0, 2/4, 1/10);
        $this->mossSnapToCeiling = new SimplexF($randomSourceProvider->identical(), 2.0, 2/4, 1/30);
        $this->sculkPatch = new SimplexF($randomSourceProvider->identical(), 20.0, 1/99, 1/100);
        $this->kelp = new SimplexNoise($randomSourceProvider->identical(), -7, [1]);
    }

    public function getRandomClayWithDripleavesSnapToFloor(): SimplexF { return $this->randomClayWithDripleavesSnapToFloor; }
    public function getDripstoneCluster(): SimplexF { return $this->dripstoneCluster; }
    public function getMossPatchSnapToFloor(): SimplexF { return $this->mossPatchSnapToFloor; }
    public function getMossSnapToCeiling(): SimplexF { return $this->mossSnapToCeiling; }
    public function getSculkPatch(): SimplexF { return $this->sculkPatch; }
    public function getKelp(): SimplexNoise { return $this->kelp; }
}