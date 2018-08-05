<?php

namespace Minifixio\onevsone\utils;

// Pocketmine imports
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
	 */
	public static function logOnConsole(string $message){
		$logger = Server::getInstance()->getLogger();
		$logger->info(OneVsOne::getMessage("pluginprefix ") . $message);
	}

	public static function sendDefaultMessage(Player $player, string $message){
		$player->sendMessage(TextFormat::GOLD . TextFormat::BOLD . OneVsOne::getMessage("pluginprefix ") . TextFormat::WHITE . $message);
	}
}



