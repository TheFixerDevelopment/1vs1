<?php

namespace Minifixio\onevsone\utils;

// Pocketmine imports
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

// Plugin imports
use Minifixio\onevsone\OneVsOne;


/**
 * Utility methods for 1vs1 plugin
 */
class PluginUtils{

    /**
     * Log on the server console
     *
     * @param string $message
     */
    public static function logOnConsole(string $message){
        Server::getInstance()->getLogger()->info($message);
    }

    /**
     * @param Player $player
     * @param string $message
     */
    public static function sendDefaultMessage(Player $player, string $message){
        $player->sendMessage(TextFormat::RESET . OneVsOne::getMessage("pluginprefix ") . TextFormat::RESET . $message);
    }
}



