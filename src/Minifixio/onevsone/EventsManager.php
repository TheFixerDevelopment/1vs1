<?php

namespace Minifixio\onevsone;

// Pocketmine imports
use pocketmine\tile\{Sign, Tile};
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDeathEvent};
use pocketmine\event\player\{PlayerJoinEvent, PlayerInteractEvent, PlayerQuitEvent, PlayerDeathEvent};
use pocketmine\event\block\SignChangeEvent;
use pocketmine\utils\TextFormat;

//Plugin imports
use Minifixio\onevsone\utils\PluginUtils;



/**
 * Manages PocketMineEvents
 */
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
		if($arena != NULL){
                        $event->setDrops([]);
                        $event->setKeepInventory(false);
			$arena->onPlayerDeath($deadPlayer);
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
					$event->setLine(1,"-Waiting: "  . $this->arenaManager->getNumberOfPlayersInQueue());
					$event->setLine(2,"-Arenas:" . $this->arenaManager->getNumberOfFreeArenas());
					$event->setLine(3,"-+===+-");
					return true;
				}
			}
		}
	}
public function onInteract(PlayerInteractEvent $e) {
    if($e->getAction() == PlayerInteractEvent::LEFT_CLICK_BLOCK) {
    $block = $e->getBlock()->getId();
      if($block == Item::SIGN_POST || $block == Block::SIGN_POST || $block == Block::WALL_SIGN) {
         $tile = $e->getPlayer()->getLevel()->getTile($e->getBlock()->getTile());
            $this->arenaManager->addNewPlayerToQueue($e->getPlayer());
          }
       }
    }



}