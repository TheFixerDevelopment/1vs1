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

    public const SIGN_TITLE = '[1vs1]';
	
	/** @var string */
	private const CONFIG_VER = "1.0.0";
	
	private const MESSAGES_VER = "1.0.0";
	
	/**
	 * Check if the config is up-to-date.
	 */
	public function ConfigCheck(): void{
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		if((!$config->exists("config-version")) || ($config->get("config-version") !== self::CONFIG_VER)){
			rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config_old.yml");
			$this->saveResource("config.yml");
			$this->getLogger()->critical("Your configuration file is outdated.");
			$this->getLogger()->notice("Your old configuration has been saved as config_old.yml and a new configuration file has been generated.");
			return;
		}
	}
	/**
	 * Check if the config is up-to-date.
	 */
	public function MessagesCheck(): void{
		$message = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
		if((!$config->exists("messages-version")) || ($config->get("message-version") !== self::MESSAGES_VER)){
			rename($this->getDataFolder() . "messages.yml", $this->getDataFolder() . "messages_old.yml");
			$this->saveResource("messages.yml");
			$this->getLogger()->critical("Your configuration file is outdated.");
			$this->getLogger()->notice("Your old configuration has been saved as messages_old.yml and a new configuration file has been generated.");
			return;
		}
	}
    /**
     * Plugin is enabled by PocketMine server
     */
    public function onEnable(): void {
		$this->ConfigCheck();
		$this->MessagesCheck();
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
