<?php
declare(strict_types=1);
namespace Minifixio\onevsone\Tasks;


use Minifixio\onevsone\Arena;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Minidixio\onevsone\OneVsOne;

class GameTimeTask extends Task{
	
	private $roundDuration;

	/** @var OneVsOne  */
	private $plugin;
	/** @var Arena  */
	private $arena;
	
	public function __construct(OneVsOne $plugin, Arena $arena){
		$this->plugin = $plugin;
		$this->arena = $arena;
		$this->roundDuration= $owner->getConfig()->get("time-limit") * 60;
	}
	
	public function onRun(int $currentTick) : void {
		if(count($this->arena->players) < 2){
			$this->arena->abortDuel();
		}
		else{
		    /** @var Player $player1 */
            $player1 = $this->arena->players[0];
            /** @var Player $player2 */
			$player2 = $this->arena->players[1];
			if(!$player1->isOnline() || !$player2->isOnline()){
				$this->arena->abortDuel();
			}
			else{
				$player1->sendPopup(TextFormat::RESET . str_replace("{roundtime}", $this->roundDuration, OneVsOne::getMessage("round_duration")));
				$player2->sendPopup(TextFormat::RESET . str_replace("{roundtime}", $this->roundDuration, OneVsOne::getMessage("round_duration")));
				$this->roundDuration--;
				
				// If duration is exceeded, end match.
				if($this->roundDuration == 0){
					$this->arena->onRoundEnd();
				}
			}
		}
	}
	
}
