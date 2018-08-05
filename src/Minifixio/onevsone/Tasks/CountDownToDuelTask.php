<?php

namespace Minifixio\onevsone\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use Minifixio\onevsone\OneVsOne;


class CountDownToDuelTask extends Task{
	
	const COUNTDOWN_DURATION = 5;
	
	private $arena;
	private $countdownValue;
	
	public function __construct(Plugin $owner, Arena $arena){
		$this->owner = $owner;
		$this->arena = $arena;
		$this->countdownValue = CountDownToDuelTask::COUNTDOWN_DURATION;
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
				$player1->sendTip(TextFormat::GOLD . TextFormat::BOLD . str_replace("{CD}", ($this->countdownValue . TextFormat::RESET . OneVsOne::getMessage("countdown_timer"))));
				$player2->sendTip(TextFormat::GOLD . TextFormat::BOLD . str_replace("{CD}", ($this->countdownValue . TextFormat::RESET . OneVsOne::getMessage("countdown_timer"))));
				$this->countdownValue--;
				
				// If countdown is finished, start the duel and stop the task
				if($this->countdownValue == 0){
					$this->arena->startDuel();
				}
			}
		}
	}
	
}