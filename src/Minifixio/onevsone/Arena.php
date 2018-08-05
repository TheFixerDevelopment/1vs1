<?php

namespace Minifixio\onevsone;

//Pocketmine imports
use pocketmine\{Server, Player};
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\entity\{InstantEffect, Effect};
use pocketmine\math\Vector3;
use pocketmine\level\particle\{DestroyBlockParticle, SmokeParticle};
use pocketmine\block\Block;

//Plugin imports
use Minifixio\onevsone\{ArenaManager, OneVsOne};
use Minifixio\onevsone\utils\PluginUtils;

//Date imports.
use \DateTime;

class Arena{

	public $active = FALSE;
	
	public $startTime;
	
	public $players = array();
	
	/** @var Position */
	public $position;
	
        /** @var Position */
	public $spawn1;

	/** @var Position */
	public $spawn2;
        
	/** @var ArenaManager */
	private $manager;
	
	// Roound duration (3min)
	 // Default match duration
	private $duration = 180;
        
	// Variable for stop the round's timer
	private $taskHandler;
	private $countdownTaskHandler;

	/**
	 * Build a new Arena
	 * @param Position position Base position of the Arena
	 */
	public function __construct(Location $spawn1, Location $spawn2, ArenaManager $manager){
		$this->spawn1 = $spawn1;
       $this->spawn2 = $spawn2;
		$this->manager = $manager;
       $this->duration = OneVsOne::getInstance()->getConfig()->get("time-limit") * 60;
		$this->active = FALSE;
	}
	/** 
	 * Demarre un match.
	 * @param Player[] $players
	 */
	public function startRound(array $players){
		
		// Set active to prevent new players
		$this->active = TRUE;
		
		// Set players
		$this->players = $players;
		$player1 = $players[0];
		$player2 = $players[1];
		
		$player1->sendMessage(OneVsOne::getMessage("duel_against") . $player2->getName());
		$player2->sendMessage(OneVsOne::getMessage("duel_against") . $player1->getName());
                
                //imjay addition
		 $task = new GameTime(OneVsOne::getInstance(), $this);
		$this->countdownTaskHandler = $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);

		// Create a new countdowntask
		$task = new CountDownToDuelTask(OneVsOne::getInstance(), $this);
		$this->countdownTaskHandler = $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);	
	}
	
	/**
	 * Really starts the duel after countdown
	 */
	public function startDuel(){
		
		$this->plugin->getScheduler()->cancelTask($this->countdownTaskHandler->getTaskId());
		
		$player1 = $this->players[0];
		$player2 = $this->players[1];
		
		$pos_player1 = $this->spawn1;
		$pos_player2 = $this->spawn2;
       //Need a new system for turning heads.
       //Maybe save the yaw and pitch in config?
		$player1->teleport($pos_player1);
		$player2->teleport($pos_player2);
		$this->sparyParticle($player1);
		$this->sparyParticle($player2);
		$player1->setGamemode(0);
		$player2->setGamemode(0);
		
		// Give kit
                if(OneVsOne::getInstance()->getConfig()->get("force-kit") === true) {
		foreach ($this->players as $player){
			$this->giveKit($player);
		}
		
		// Fix start time
		$this->startTime = new DateTime('now');
		
		$player1->sendTip(OneVsOne::getMessage("duel_tip"));
		$player1->sendMessage(OneVsOne::getMessage("duel_start"));
		
		$player2->sendTip(OneVsOne::getMessage("duel_tip"));
		$player2->sendMessage(OneVsOne::getMessage("duel_start"));
		
		// Launch the end round task
		$task = new RoundCheckTask(OneVsOne::getInstance());
		$task->arena = $this;
		$this->taskHandler = $this->plugin->getScheduler()->scheduleDelayedTask($task, $this->duration * 20);
	}
        }
	/**
	 * Abort duel during countdown if one of the players has quit
	 */
	public function abortDuel(){
		$this->plugin->getScheduler()->cancelTask($this->countdownTaskHandler->getTaskId());
	}
	
	private function giveKit(Player $player){
		// Clear inventory
		$player->getInventory()->clearAll();
		
		// Give sword, and food
		$player->getInventory()->addItem(Item::get(ITEM::IRON_SWORD));
		$player->getInventory()->addItem(Item::get(ITEM::BREAD));
		$player->getInventory()->setItemInHand(Item::get(ITEM::IRON_SWORD), $player);
		
		// Pur the armor on the player
		$player->getArmorInventory()->setHelmet(Item::get(302, 0, 1)); //Should be configurable soon.
		$player->getArmorInventory()->setChestplate(Item::get(303, 0, 1)); //Should be configurable soon.
		$player->getArmorInventory()->setLeggings(Item::get(304, 0, 1)); //Should be configurable soon.
		$player->getArmorInventory()->setBoots(Item::get(305, 0, 1)); //Should be configurable soon.
		$player->getArmorInventory()->sendContents($player);
		
		// Set his life to 20
		$player->setHealth(20);
		$player->removeAllEffects();

   }
   
   /**
    * When a player was killed
    * @param Player $loser
    */
   public function onPlayerDeath(Player $loser){
   	
		// Finish the duel and teleport the winner at spawn
   		if($loser == $this->players[0]){
   			$winner = $this->players[1];
   		}
   		else{
   			$winner = $this->players[0];
   		}  		
   		$loser->sendMessage(OneVsOne::getMessage("duel_loser") . $winner->getName());
   		$loser->removeAllEffects();
   		
   		$winner->sendMessage( OneVsOne::getMessage("duel_winner") . $loser->getName());
   		$winner->removeAllEffects();
   		
   		// Teleport the winner at spawn
   		$winner->teleport($winner->getSpawn());

   		// Set his life to 20
   		$winner->setHealth(20);
   		Server::getInstance()->broadcastMessage(TextFormat::GREEN . TextFormat::BOLD . "» " . TextFormat::GOLD . $winner->getName() . TextFormat::WHITE . OneVsOne::getMessage("duel_broadcast") . TextFormat::RED . $loser->getName() . TextFormat::WHITE . " !");
   		
   		// Reset arena
   		$this->reset();
   }

   /**
    * Reset the Arena to current state
    */
   private function reset(){
   		// Put active arena after the duel
   		$this->active = FALSE;
                if(OneVsOne::getInstance()->getConfig()->get("force-kit") === true) {
   		foreach ($this->players as $player){
   			$player->getInventory()->setItemInHand(new Item(Item::AIR,0,0));
   			$player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->getArmorInventory()->sendContents($player);
   			$player->getInventory()->sendContents($player);
   			$player->getInventory()->sendHeldItem($player);
   		}
   		$this->players = array();
   		$this->startTime = NULL;
   		if($this->taskHandler != NULL){
   			$this->plugin->getScheduler()->cancelTask($this->taskHandler->getTaskId());
   			$this->manager->notifyEndOfRound($this);
   		}
   }
   }
   /**
    * When a player quit the game
    * @param Player $loser
    */
   public function onPlayerQuit(Player $loser){
   		// Finish the duel when a player quit
   		// With onPlayerDeath() function
   		$this->onPlayerDeath($loser);
   }
   
   /**
    * When maximum round time is reached
    */
   public function onRoundEnd(){
   		foreach ($this->players as $player){
   			$player->teleport($player->getSpawn());
   			$player->sendMessage(TextFormat::BOLD . "++++++++=++++++++");
   			$player->sendMessage(OneVsOne::getMessage("duel_timeover"));
   			$player->sendMessage(TextFormat::BOLD . "++++++++=++++++++");
   			$player->removeAllEffects();
                        $player->getArmorInventory()->clearAll();
                        $player->getInventory()->clearAll();
   		}
   		
   		// Reset arena
   		$this->reset();   		
	 }
	 
	 public function isPlayerInArena(Player $player){
	 	return in_array($player, $this->players);
	 }
	 
	 public function sparyParticle(Player $player){
		$particle = new DestroyBlockParticle(new Vector3($player->getX(), $player->getY(), $player->getZ()), Block::get(8));
	 	$player->getLevel()->addParticle($particle);
    }
}



