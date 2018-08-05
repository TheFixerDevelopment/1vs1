<?php

namespace Minifixio\onevsone\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;

class GameTimeTask extends PluginTask{
	
	private $roundDuration = 180;
	
	private $arena;
	private $countdownValue;
	
	public function __construct(Plugin $owner, Arena $arena){
		$this->owner = $owner;
		$this->arena = $arena;
		$this->countdownValue = $owner->getConfig()->get("time-limit") * 60;
	}
	
	public function onRun(int $currentTick): void{
		if(count($this->arena->players) < 2){
			$this->arena->abortDuel();
		}
		else{
			$player1 = $this->arena->players[0];
			$player2 = $this->arena->players[1];
			
			if(!$player1->isOnline() || !$player2->isOnline()){
				$this->arena->abortDuel();
			}
			else{
				$player1->sendPopup(TextFormat::GOLD . TextFormat::BOLD . "Battle Ends in " . $this->countdownValue . TextFormat::RESET . " seconds"); //Make this configurable in the future.
				$player2->sendPopup(TextFormat::GOLD . TextFormat::BOLD . "Battle Ends in " . $this->countdownValue . TextFormat::RESET . " seconds"); //Make this configurable in the future.
				$this->countdownValue--;
				
				// If countdown is finished, start the duel and stop the task
				if($this->countdownValue == 0){
					$this->arena->onRoundEnd();
				}
			}
		}
	}
	
}
