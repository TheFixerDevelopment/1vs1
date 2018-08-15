<?php

namespace Minifixio\onevsone\Commands;

//Pocketmine imports
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

//Plugin imports
use Minifixio\onevsone\{ArenaManager, OneVsOne};

class JoinCommand extends Command{

    private $plugin;
    private $arenaManager;
    public $commandName = "match";

    public function __construct(OneVsOne $plugin, ArenaManager $arenaManager){
        parent::__construct($this->commandName, "Join 1vs1 queue !");
        $this->setUsage("/$this->commandName");

        $this->plugin = $plugin;
        $this->arenaManager = $arenaManager;
    }

    public function execute(CommandSender $sender, string $label, array $params): bool{
        if(!$this->plugin->isEnabled()){
            return false;
        }

        if(!$sender instanceof Player){
            $sender->sendMessage(OneVsOne::getMessage("console_only"));
            return true;
        }

        $this->arenaManager->addNewPlayerToQueue($sender);

        return true;
    }
}