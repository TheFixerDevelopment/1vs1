<?php

namespace Minifixio\onevsone;

// Pocketmine imports
use pocketmine\tile\Sign;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerInteractEvent, PlayerQuitEvent, PlayerDeathEvent};
use pocketmine\event\block\SignChangeEvent;


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
            $event->setKeepInventory(false);
            $arena->onPlayerDeath($deadPlayer);
        }
    }

    public function tileupdate(SignChangeEvent $event){
        if($event->getBlock()->getID() == Item::SIGN_POST || $event->getBlock()->getID() == Block::SIGN_POST || $event->getBlock()->getID() == Block::WALL_SIGN){
            $signTile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if(!$signTile instanceof Sign){
                return false;
            }
            $signLines = $event->getLines();
            if($signLines[0] == OneVsOne::SIGN_TITLE){
                if($event->getPlayer()->isOp()){
                    $this->arenaManager->addSign($signTile);
                    $event->setLine(1, "-Waiting: " . $this->arenaManager->getNumberOfPlayersInQueue());
                    $event->setLine(2, "-Arenas:" . $this->arenaManager->getNumberOfFreeArenas());
                    $event->setLine(3, "-+===+-");
                    return true;
                }
            }
        }
        return false;
    }

    public function onInteract(PlayerInteractEvent $e){
        if($e->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            $id = ($b = $e->getBlock())->getId();
            if(in_array($id, [Block::SIGN_POST, Block::WALL_SIGN])){
                foreach($this->arenaManager->config->get("signs") as $sign => $pos){
                    if($pos = [$b->x, $b->y, $b->z, $b->level]){
                        $this->arenaManager->addNewPlayerToQueue($e->getPlayer());
                    }
                }
            }
        }
    }
}