<?php

namespace Minifixio\onevsone\Tasks;

// Pocketmine imports
use Minifixio\onevsone\Arena;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

// Plugin imports
use Minifixio\onevsone\OneVsOne;


class CountDownToDuelTask extends Task{

   public const COUNTDOWN_DURATION = 5;

    private $arena;
    
    private $plugin;
    
    private $countdownValue;

    public function __construct(OneVsOne $plugin, Arena $arena){
        $this->plugin = $plugin;
        $this->arena = $arena;
        $this->countdownValue = self::COUNTDOWN_DURATION;
    }
    public function onRun(int $currentTick) : void {

        if(count($this->arena->players) < 2){
            $this->arena->abortDuel();
            return;
        }
        /** @var Player $player1 */
        $player1 = $this->arena->players[0];
        /** @var Player $player2 */
        $player2 = $this->arena->players[1];
        if(!$player1->isOnline() || !$player2->isOnline()){
            $this->arena->abortDuel();
            return;
        }
        // If countdown is finished, start the duel and stop the task
        if($this->countdownValue == 0){
            $this->arena->startDuel();
        }
        $player1->sendTip(TextFormat::RESET . str_replace("{CD}", $this->countdownValue, OneVsOne::getMessage("countdown_timer")));
        $player2->sendTip(TextFormat::RESET . str_replace("{CD}", $this->countdownValue, OneVsOne::getMessage("countdown_timer")));
        $this->countdownValue--;
    }
}
