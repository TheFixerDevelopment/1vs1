<?php

namespace Minifixio\onevsone\Tasks;

// Pocketmine imports

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;


class SignRefreshTask extends Task{
	
	/** var ArenaManager **/
	public $arenaManager;
	
	public function onRun(int $currentTick) : void{
		$this->arenaManager->refreshSigns();
	}
	
}