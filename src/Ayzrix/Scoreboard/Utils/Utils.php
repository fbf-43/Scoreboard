<?php

declare(strict_types=1);

namespace Ayzrix\Scoreboard\Utils;

use Ayzrix\Scoreboard\Extensions\BankUI;
use Ayzrix\Scoreboard\Extensions\Bounty;
use Ayzrix\Scoreboard\Extensions\CoinsSystem;
use Ayzrix\Scoreboard\Extensions\CombatLogger;
use Ayzrix\Scoreboard\Extensions\EconomyAPI;
use Ayzrix\Scoreboard\Extensions\FactionMaster;
use Ayzrix\Scoreboard\Extensions\FactionsPro;
use Ayzrix\Scoreboard\Extensions\FightLogger;
use Ayzrix\Scoreboard\Extensions\Godmode;
use Ayzrix\Scoreboard\Extensions\KDR;
use Ayzrix\Scoreboard\Extensions\MultiEconomy;
use Ayzrix\Scoreboard\Extensions\MultiServerCounter;
use Ayzrix\Scoreboard\Extensions\MyPlot;
use Ayzrix\Scoreboard\Extensions\OnlineTime;
use Ayzrix\Scoreboard\Extensions\PiggyFaction;
use Ayzrix\Scoreboard\Extensions\Prisons;
use Ayzrix\Scoreboard\Extensions\PurePerms;
use Ayzrix\Scoreboard\Extensions\RankSystem;
use Ayzrix\Scoreboard\Extensions\RedSkyBlock;
use Ayzrix\Scoreboard\Extensions\SeeDevice;
use Ayzrix\Scoreboard\Extensions\SimpleFaction;
use Ayzrix\Scoreboard\Extensions\Skyblock;
use Ayzrix\Scoreboard\Extensions\VanishV2;
use Ayzrix\Scoreboard\Extensions\VoteParty;
use Ayzrix\Scoreboard\Main;
use pocketmine\player\Player;
use pocketmine\Server;

class Utils{

    /**
     * @return mixed
     */
    public static function getIntoConfig(string $value){
        return Main::getInstance()->getConfig()->get($value);
    }

    public static function formateString(Player $player, string $string): string{

        $server = Server::getInstance();

        $world = $player->getWorld();
        $position = $player->getPosition();
        $item = $player->getInventory()->getItemInHand();

        $replacements = [
            "{ping}" => $player->getNetworkSession()->getPing(),
            "{tps}" => $server->getTicksPerSecond(),
            "{name}" => $player->getName(),
            "{online}" => count($server->getOnlinePlayers()),
            "{max_online}" => $server->getMaxPlayers(),

            "{level}" => $world->getFolderName(),

            "{x}" => round($position->getX()),
            "{y}" => round($position->getY()),
            "{z}" => round($position->getZ()),

            "{ip}" => $player->getNetworkSession()->getIp(),
            "{port}" => $player->getNetworkSession()->getPort(),

            "{uid}" => $player->getUniqueId()->toString(),
            "{xuid}" => $player->getXuid(),

            "{health}" => $player->getHealth(),
            "{max_health}" => $player->getMaxHealth(),

            "{food}" => $player->getHungerManager()->getFood(),
            "{max_food}" => 20,

            "{gamemode}" => $player->getGamemode()->getName(),
            "{scale}" => $player->getScale(),

            "{xplevel}" => $player->getXpManager()->getXpLevel(),

            "{id}" => $item->getTypeId(),
            "{meta}" => $item->getStateId(),
            "{count}" => $item->getCount(),

            "{date}" => date(
                (string) Utils::getIntoConfig("date_format")
            ),
        ];

        $string = str_replace(
            array_keys($replacements),
            array_map(
                static fn($value): string => (string) $value,
                array_values($replacements)
            ),
            $string
        );

        /*
         * Factions
         */
        if(Main::$options["PiggyFactions"] ?? false){
            $string = str_replace(
                ["{faction_name}", "{faction_rank}", "{faction_power}"],
                [
                    PiggyFaction::getPlayerFaction($player),
                    PiggyFaction::getPlayerRank($player),
                    PiggyFaction::getFactionPower($player)
                ],
                $string
            );
        }

        if(Main::$options["FactionsPro"] ?? false){
            $string = str_replace(
                ["{faction_name}", "{faction_power}"],
                [
                    FactionsPro::getPlayerFaction($player),
                    FactionsPro::getFactionPower($player)
                ],
                $string
            );
        }

        if(Main::$options["SimpleFaction"] ?? false){
            $string = str_replace(
                [
                    "{faction_name}",
                    "{faction_rank}",
                    "{faction_power}",
                    "{faction_money}"
                ],
                [
                    SimpleFaction::getPlayerFaction($player),
                    SimpleFaction::getPlayerRank($player),
                    SimpleFaction::getFactionPower($player),
                    SimpleFaction::getFactionMoney($player)
                ],
                $string
            );
        }

        /*
         * Economy
         */
        if(Main::$options["EconomyAPI"] ?? false){
            $string = str_replace(
                ["{money}"],
                [EconomyAPI::getMoney($player)],
                $string
            );
        }

        /*
         * Permissions / Rank
         */
        if(Main::$options["PurePerms"] ?? false){
            $string = str_replace(
                ["{rank}", "{prefix}", "{suffix}"],
                [
                    PurePerms::getPlayerRank($player),
                    PurePerms::getPlayerPrefix($player),
                    PurePerms::getPlayerSuffix($player)
                ],
                $string
            );
        }

        if(Main::$options["RankSystem"] ?? false){
            $string = str_replace(
                ["{rank}", "{prefix}"],
                [
                    RankSystem::getPlayerRank($player),
                    RankSystem::getPlayerPrefix($player)
                ],
                $string
            );
        }

        /*
         * SkyBlock
         */
        if(Main::$options["SkyBlock"] ?? false){
            $string = str_replace(
                [
                    "{island_blocks}",
                    "{island_members}",
                    "{island_rank}",
                    "{island_size}"
                ],
                [
                    Skyblock::getIslandBlocks($player),
                    Skyblock::getIslandMembers($player),
                    Skyblock::getIslandRank($player),
                    Skyblock::getIslandSize($player)
                ],
                $string
            );
        }

        /*
         * Device
         */
        if(Main::$options["SeeDevice"] ?? false){
            $string = str_replace(
                ["{device}"],
                [SeeDevice::getPlayerOs($player)],
                $string
            );
        }

        /*
         * Bounty
         */
        if(Main::$options["Bounty"] ?? false){
            $string = str_replace(
                ["{bounty}"],
                [Bounty::getPlayerBounty($player)],
                $string
            );
        }

        /*
         * Prisons
         */
        if(Main::$options["Prisons"] ?? false){
            $string = str_replace(
                ["{prisons_rank}", "{prisons_prestige}"],
                [
                    Prisons::getPlayerRank($player),
                    Prisons::getPlayerPrestige($player)
                ],
                $string
            );
        }

        /*
         * OnlineTime
         */
        if(Main::$options["OnlineTime"] ?? false){
            $string = str_replace(
                ["{onlinetime_session}", "{onlinetime_total}"],
                [
                    OnlineTime::getSessionTime($player),
                    OnlineTime::getTotalTime($player)
                ],
                $string
            );
        }

        /*
         * Combat
         */
        if(Main::$options["CombatLogger"] ?? false){
            $string = str_replace(
                ["{combatlogger_time}"],
                [CombatLogger::getTaggedTime($player)],
                $string
            );
        }

        if(Main::$options["FightLogger"] ?? false){
            $string = str_replace(
                ["{fightlogger_time}"],
                [FightLogger::getTaggedTime($player)],
                $string
            );
        }

        /*
         * MyPlot
         */
        if(Main::$options["MyPlot"] ?? false){
            $string = str_replace(
                ["{myplot_owner}", "{myplot_id}"],
                [
                    MyPlot::getPlotOwner($player),
                    MyPlot::getPlotID($player)
                ],
                $string
            );
        }

        /*
         * Coins
         */
        if(Main::$options["CoinsSystem"] ?? false){
            $string = str_replace(
                ["{coins}"],
                [CoinsSystem::getPlayerCoins($player)],
                $string
            );
        }

        /*
         * KDR
         */
        if(Main::$options["KDR"] ?? false){
            $string = str_replace(
                ["{kills}", "{deaths}", "{kdr}"],
                [
                    KDR::getPlayerKills($player),
                    KDR::getPlayerDeaths($player),
                    KDR::getPlayerKDR($player)
                ],
                $string
            );
        }

        /*
         * VoteParty
         */
        if(Main::$options["VoteParty"] ?? false){
            $string = str_replace(
                ["{votes}", "{maxvotes}"],
                [
                    VoteParty::getVotes(),
                    VoteParty::getMaxVotes()
                ],
                $string
            );
        }

        /*
         * Bank
         */
        if(Main::$options["BankUI"] ?? false){
            $string = str_replace(
                ["{balance}"],
                [BankUI::getPlayerBalance($player)],
                $string
            );
        }

        /*
         * RedSkyBlock
         */
        if(Main::$options["RedSkyBlock"] ?? false){
            $string = str_replace(
                [
                    "{island_members}",
                    "{island_rank}",
                    "{island_size}",
                    "{island_value}",
                    "{island_locked_status}"
                ],
                [
                    RedSkyBlock::getIslandMembers($player),
                    RedSkyBlock::getIslandRank($player),
                    RedSkyBlock::getIslandSize($player),
                    RedSkyBlock::getIslandValue($player),
                    RedSkyBlock::getIslandLocked($player)
                ],
                $string
            );
        }

        /*
         * Vanish
         */
        if(Main::$options["VanishV2"] ?? false){
            $string = str_replace(
                ["{vanish_fake_count}"],
                [VanishV2::getFakeCount()],
                $string
            );
        }

        /*
         * MultiEconomy
         */
        if(Main::$options["MultiEconomy"] ?? false){
            $tags = MultiEconomy::getAllTags($player);

            if(isset($tags[0], $tags[1])){
                $string = str_replace(
                    $tags[0],
                    $tags[1],
                    $string
                );
            }
        }

    
        if(Main::$options["MultiServerCounter"] ?? false){
            $string = str_replace(
                ["{MultiServer.online}", "{MultiServer.Maxonline}"],
                [
                    MultiServerCounter::getPlayerCount(),
                    MultiServerCounter::getMaxPlayerCount()
                ],
                $string
            );
        }

        if(Main::$options["Godmode"] ?? false){
            $string = str_replace(
                ["{god}"],
                [Godmode::isPlayerGod($player)],
                $string
            );
        }

        if(Main::$options["FactionMaster"] ?? false){
            $string = str_replace(
                [
                    "{faction_name}",
                    "{faction_rank}",
                    "{faction_power}",
                    "{faction_level}",
                    "{faction_xp}",
                    "{faction_message}",
                    "{faction_description}",
                    "{faction_visibility}"
                ],
                [
                    FactionMaster::getPlayerFaction($player),
                    FactionMaster::getPlayerRank($player),
                    FactionMaster::getFactionPower($player),
                    FactionMaster::getFactionLevel($player),
                    FactionMaster::getFactionXp($player),
                    FactionMaster::getFactionMessage($player),
                    FactionMaster::getFactionDescription($player),
                    FactionMaster::getFactionVisibility($player)
                ],
                $string
            );
        }

        return $string;
    }

    public static function convertMoney(float $money): string{
        $suffixes = [
            '',
            'k',
            'M',
            'B',
            'T',
            'q',
            'Q',
            's',
            'S'
        ];

        $suffixIndex = 0;

        while(abs($money) >= 1000 && $suffixIndex < 8){
            $suffixIndex++;
            $money /= 1000;
        }

        return (
            $money > 0
                ? floor($money * 1000) / 1000
                : ceil($money * 1000) / 1000
        ) . $suffixes[$suffixIndex];
    }
}
