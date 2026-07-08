<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\util\math\RandomSourceProvider;
use lolfosjo\voverworld\generator\densityfunction\FlatCacheMarker;

class NormalObjectHolder extends RandomizedObjectHolder {
    private BiomeHolder $biomeHolder;
    private TerrainHolder $terrainHolder;
    private SurfaceHolder $surfaceHolder;
    private SurfaceOverwriteHolder $surfaceOverwriteHolder;
    private FeatureHolder $featureHolder;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        parent::__construct($randomSourceProvider);
        $this->biomeHolder = new BiomeHolder($randomSourceProvider);
        $this->terrainHolder = new TerrainHolder($randomSourceProvider, $this->biomeHolder);
        $this->surfaceHolder = new SurfaceHolder($randomSourceProvider);
        $this->surfaceOverwriteHolder = new SurfaceOverwriteHolder($randomSourceProvider);
        $this->featureHolder = new FeatureHolder($randomSourceProvider);
    }

    public function getBiomeHolder(): BiomeHolder { return $this->biomeHolder; }
    public function getTerrainHolder(): TerrainHolder { return $this->terrainHolder; }
    public function getSurfaceHolder(): SurfaceHolder { return $this->surfaceHolder; }
    public function getSurfaceOverwriteHolder(): SurfaceOverwriteHolder { return $this->surfaceOverwriteHolder; }
    public function getFeatureHolder(): FeatureHolder { return $this->featureHolder; }
    public function getFlatCacheMarker(): FlatCacheMarker { return $this->terrainHolder->getFlatCacheMarker(); }
}