<?php

namespace lolfosjo\voverworld;

use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\GeneratorManager;
use lolfosjo\voverworld\generator\TestGenerator;

class Main extends PluginBase {

    private static Main $instance;

    public function onLoad(): void {
        self::$instance = $this;

        $generatorManager = GeneratorManager::getInstance();
        $generatorManager->addGenerator(TestGenerator::class, "terrain_test", fn() => null);
    }
    
    public static function getInstance() : Main{
        return self::$instance;
    }
}