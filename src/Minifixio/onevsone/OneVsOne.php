<?php

namespace Minifixio\onevsone;

//Pocketmine imports
use pocketmine\utils\{TextFormat, Config};
use pocketmine\plugin\PluginBase;

//Plugin imports
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\Commands\{ArenaCommand, JoinCommand};


class OneVsOne extends PluginBase{

    /** @var OneVsOne */
    private static $instance;

    /** @var ArenaManager */
    public $arenaManager;

    /** @var Config */
    public $arenaConfig;

    /** @var Config */
    public $messages;

    public CONST SIGN_TITLE = '[1vs1]';

    /**
     * Plugin is enabled by PocketMine server
     */
    public function onEnable(): void {
        self::$instance = $this;
        PluginUtils::logOnConsole(TextFormat::GREEN . "Init" . TextFormat::RED . " 1vs1 " . TextFormat::GREEN . "plugin");

        @mkdir($this->getDataFolder());

        $this->arenaConfig = new Config($this->getDataFolder() . "data.yml", Config::YAML, array());

        // Load custom messages
        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder() . "messages.yml");

        $this->saveDefaultConfig();

        $this->arenaManager = new ArenaManager();
        $this->arenaManager->init($this->arenaConfig);

        // Register events
        $this->getServer()->getPluginManager()->registerEvents(new EventsManager($this->arenaManager), $this);

        // Register commands
        $joinCommand = new JoinCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($joinCommand->commandName, $joinCommand);

        $arenaCommand = new ArenaCommand($this, $this->arenaManager);
        $this->getServer()->getCommandMap()->register($arenaCommand->commandName, $arenaCommand);
    }

    public function getPrefix(): string {
        $prefix = $this->messages->get("pluginprefix");
        $finalPrefix = str_replace("&", "§", $prefix);
        return $finalPrefix . " ";
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public static function getMessage(string $message = ""): string {
        if(($msg = self::getInstance()->messages->get($message)) !== null){
            $finalMessage = str_replace("&", "§", $msg);
            return $finalMessage;
        }else{
            return null;
        }
    }
}
