<?php

namespace Minifixio\onevsone;

// Pocketmine imports
use pocketmine\tile\Sign;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerQuitEvent, PlayerDeathEvent};
use pocketmine\event\block\SignChangeEvent;

//Plugin imports
use Minifixio\onevsone\OneVsOne;

class EventsManager implements Listener{

    /** @var ArenaManager */
    private $arenaManager;

    public function __construct(ArenaManager $arenaManager){
        $this->arenaManager = $arenaManager;
    }

    public function onPlayerQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $this->arenaManager->removePlayerFromQueueOrArena($player);
    }

    public function onPlayerDeath(PlayerDeathEvent $event){
        $deadPlayer = $event->getEntity();
        $arena = $this->arenaManager->getPlayerArena($deadPlayer);
        if($arena != null){
            $event->setDrops([]);
			if(OneVsOne::getInstance()->getConfig()->get("keep-inventory") === false) {
            $event->setKeepInventory(false);
            $arena->onPlayerDeath($deadPlayer);
        }
   }
    }
   public function tileupdate(SignChangeEvent $event){
		if($event->getBlock()->getID() == Item::SIGN_POST || $event->getBlock()->getID() == Block::SIGN_POST || $event->getBlock()->getID() == Block::WALL_SIGN){
			$signTile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($signTile instanceof Sign)){
				return true;
			}
			$signLines = $event->getLines();
			if($signLines[0]== OneVsOne::SIGN_TITLE){
				if($event->getPlayer()->isOp()){
					$this->arenaManager->addSign($signTile);
					$event->setLine(1,"-Waiting: "  . $this->arenaManager->getNumberOfPlayersInQueue()); //To-Do make this configurable.
					$event->setLine(2,"-Arenas:" . $this->arenaManager->getNumberOfFreeArenas()); //To-Do make this configurable.
					$event->setLine(3,"-+===+-");
					return true;
				}
			}
		}
	}
}