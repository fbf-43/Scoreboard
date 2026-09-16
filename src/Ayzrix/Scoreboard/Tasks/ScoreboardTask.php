<?php

declare(strict_types=1);

namespace Ayzrix\Scoreboard\Tasks;

use Ayzrix\Scoreboard\Events\Listener\PlayerListener;
use Ayzrix\Scoreboard\Utils\Utils;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardTask extends Task{

    public function onRun() : void{
        $server = Server::getInstance();

        foreach(PlayerListener::$scoreboards as $name => $scoreboard){

            $player = $server->getPlayerExact($name);

            if(!$player instanceof Player){
                unset(PlayerListener::$scoreboards[$name]);
                continue;
            }

            if(Utils::getIntoConfig("per_world") === false){

                $scoreboard->setDisplayName(
                    (string) Utils::getIntoConfig("title")
                );

                $i = 0;

                foreach(Utils::getIntoConfig("lines") as $line){
                    $line = Utils::formateString(
                        $player,
                        (string) $line
                    );

                    $scoreboard->setLine($i, $line);
                    $i++;
                }

                $scoreboard->set();

            }else{

                $worldName = $player->getWorld()->getFolderName();

                $worlds = Utils::getIntoConfig("worlds");

                if(isset($worlds[$worldName])){

                    $worldConfig = $worlds[$worldName];

                    $scoreboard->setDisplayName(
                        (string) $worldConfig["title"]
                    );

                    $i = 0;

                    foreach($worldConfig["lines"] as $line){
                        $line = Utils::formateString(
                            $player,
                            (string) $line
                        );

                        $scoreboard->setLine($i, $line);
                        $i++;
                    }

                    $scoreboard->set();
                }
            }
        }
    }
}
