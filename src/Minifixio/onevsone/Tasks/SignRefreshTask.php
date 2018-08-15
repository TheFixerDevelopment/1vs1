<?php

namespace Minifixio\onevsone\Tasks;

// Pocketmine imports

use Minifixio\onevsone\OneVsOne;
use pocketmine\scheduler\Task;


class SignRefreshTask extends Task{
	
	/** var ArenaManager **/
	public $arenaManager;

	public function __construct(OneVsOne $plugin){
	    $this->arenaManager = $plugin->arenaManager;
    }

    public function onRun(int $currentTick) : void{
		$this->arenaManager->refreshSigns();
	}
	
}