<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\holder;

use lolfosjo\voverworld\generator\noise\minecraft\noise\NormalNoise;
use lolfosjo\voverworld\util\math\RandomSourceProvider;

class BiomeHolder extends RandomizedObjectHolder {
    private NormalNoise $continentalNoise;
    private NormalNoise $temperatureNoise;
    private NormalNoise $humidityNoise;
    private NormalNoise $erosionNoise;
    private NormalNoise $weirdnessNoise;
    private NormalNoise $offsetNoise;
    private NormalNoise $jaggedNoise;

    public function __construct(RandomSourceProvider $randomSourceProvider) {
        parent::__construct($randomSourceProvider);
        $this->continentalNoise = new NormalNoise($randomSourceProvider->fork(), -9, [1, 1, 2, 2, 2, 1, 1, 1, 1]);
        $this->temperatureNoise = new NormalNoise($randomSourceProvider->fork(), -10, [1.5, 0, 1, 0, 0, 0]);
        $this->humidityNoise = new NormalNoise($randomSourceProvider->fork(), -8, [1, 1, 0, 0, 0, 0]);
        $this->erosionNoise = new NormalNoise($randomSourceProvider->fork(), -9, [1, 1, 0, 1, 1]);
        $this->weirdnessNoise = new NormalNoise($randomSourceProvider->fork(), -7, [1, 2, 1, 0, 0, 0]);
        $this->offsetNoise = new NormalNoise($randomSourceProvider->fork(), -3, [1, 1, 1, 0]);
        $this->jaggedNoise = new NormalNoise($randomSourceProvider->fork(), -16, [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]);
    }

    public function getContinentalNoise(): NormalNoise { return $this->continentalNoise; }
    public function getTemperatureNoise(): NormalNoise { return $this->temperatureNoise; }
    public function getHumidityNoise(): NormalNoise { return $this->humidityNoise; }
    public function getErosionNoise(): NormalNoise { return $this->erosionNoise; }
    public function getWeirdnessNoise(): NormalNoise { return $this->weirdnessNoise; }
    public function getOffsetNoise(): NormalNoise { return $this->offsetNoise; }
    public function getJaggedNoise(): NormalNoise { return $this->jaggedNoise; }
}