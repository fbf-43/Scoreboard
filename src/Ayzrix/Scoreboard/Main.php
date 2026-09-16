<?php

declare(strict_types=1);

namespace Ayzrix\Scoreboard;

use Ayzrix\Scoreboard\Commands\Scoreboard;
use Ayzrix\Scoreboard\Events\Listener\PlayerListener;
use Ayzrix\Scoreboard\Tasks\ScoreboardTask;
use Ayzrix\Scoreboard\Utils\Utils;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    private static ?Main $instance = null;

    public static array $options = [];

    protected function onEnable() : void{
        $this->saveDefaultConfig();

        self::$instance = $this;

        $this->getServer()->getPluginManager()->registerEvents(
            new PlayerListener(),
            $this
        );

        $this->checkDependencies();

        if(Utils::getIntoConfig("command") === true){
            $this->getServer()->getCommandMap()->register(
                "scoreboard",
                new Scoreboard($this)
            );
        }

        $this->getScheduler()->scheduleRepeatingTask(
            new ScoreboardTask(),
            (int) Utils::getIntoConfig("update_time")
        );
    }

    private function checkDependencies() : void{
        foreach(Utils::getIntoConfig("options") as $pluginName => $bool){
            if($bool === true){
                $plugin = $this->getServer()->getPluginManager()->getPlugin($pluginName);

                if($plugin === null){
                    $this->getLogger()->notice(
                        "Please download a valid version of {$pluginName}"
                    );

                    $this->getServer()->getPluginManager()->disablePlugin($this);
                    return;
                }

                self::$options[$pluginName] = true;
            }else{
                self::$options[$pluginName] = false;
            }
        }
    }

    public static function getInstance() : Main{
        if(self::$instance === null){
            throw new \LogicException("Plugin instance is not initialized");
        }

        return self::$instance;
    }
}
