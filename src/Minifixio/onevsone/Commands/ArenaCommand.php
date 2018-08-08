<?php

namespace Minifixio\onevsone\Commands;

//Pocketmine imports
use pocketmine\command\{CommandSender, Command};
use pocketmine\level\Location;
use pocketmine\{Server, Player};
use pocketmine\utils\TextFormat;

//Plugin imports
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\{ArenaManager, OneVsOne};

/**
 * Command to reference a new Arena in the pool 
 * @author Minifixio
 */
class ArenaCommand extends Command {

	private $plugin;
	private $arenaManager;
	public $commandName = "arena";
   private $pos1 = [];
   private $pos2 = [];

	public function __construct(OneVsOne $plugin, ArenaManager $arenaManager){
		parent::__construct($this->commandName, "Reference a new arena");
		$this->setUsage("/arena [1 | 2 | {name}]");
		$this->command = $this->commandName;
		$this->plugin = $plugin;
		$this->arenaManager = $arenaManager;
	}

	public function execute(CommandSender $sender, string $label, array $params) : bool{
      //Is this necessary?
		if(!$this->plugin->isEnabled()){
			return false;
		}

        if(count($params) !== 1) {
          $sender->sendMessage(TextFormat::RED . $this->getUsage());
          return true;
      }

		if(!$sender instanceof Player){
			$sender->sendMessage(OneVsOne::getMessage("console_only"));
			return true;
		}
		
		if($sender->hasPermission("onevsone.arena")){

       switch(strtolower($params[0])) {

         case "1":
           $this->pos1[$sender->getName()] = [$sender->getX(), $sender->getY(), $sender->getZ(), $sender->getYaw(), $sender->getPitch(), $sender->getLevel()];
           $sender->sendMessage($this->prefix . TextFormat::GREEN . OneVsOne::getMessage("arena_pos1"));
           break;

         case "2":
           $this->pos2[$sender->getName()] = [$sender->getX(), $sender->getY(), $sender->getZ(), $sender->getYaw(), $sender->getPitch(), $sender->getLevel()];
           $sender->sendMessage($this->prefix 
. TextFormat::GREEN . OneVsOne::getMessage("arena_pos2"));
           break;
         case "create":
           if(isset($this->pos1[$sender->getName()]) && isset($this->pos2[$sender->getName()])) {
             $pos1 = $this->pos1[$sender->getName()];
             $spawn1 = new Location($pos1[0], $pos1[1], $pos1[2], $pos1[3], $pos1[4], $pos1[5]);
             $pos2 = $this->pos2[$sender->getName()];
             $spawn2 = new Location($pos2[0], $pos2[1], $pos2[2], $pos2[3], $pos2[4], $pos2[5]);
             unset($this->pos1[$sender->getName()]);
             unset($this->pos2[$sender->getName()]);
             $this->arenaManager->referenceNewArena($spawn1, $spawn2);
             $sender->sendMessage(str_replace("{arenacount}", ($this->arenaManager->getNumberOfArenas() . TextFormat::RESET . OneVsOne::getMessage("pluginprefix "). OneVsOne::getMessage("arena_created"))));
             break;
              }
           }
       } else {
         $sender->sendMessage(TextFormat::RED . OneVsOne::getMessage("op_only"));
             }
        }
    }