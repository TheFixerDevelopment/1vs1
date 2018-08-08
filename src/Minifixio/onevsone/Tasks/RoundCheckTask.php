<?php

namespace Minifixio\onevsone\Tasks;

// Pocketmine imports
use pocketmine\scheduler\Task;


class RoundCheckTask extends Task{
	
	public $arena;
	
	public function onRun(int $currentTick) : void{
		$this->arena->onRoundEnd();
	}
	
}