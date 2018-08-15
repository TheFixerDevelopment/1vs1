<?php

namespace Minifixio\onevsone;

//Pocketmine imports
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\level\Location;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;

## Plugin imports
use Minifixio\onevsone\utils\PluginUtils;
use Minifixio\onevsone\Tasks\SignRefreshTask;

/**
 * Manages PVP arenas
 */
class ArenaManager{

    /** @var Server */
    private $server;

    /** @var Arena[] * */
    private $arenas = array();

    /** @var Player[] * */
    private $queue = array();

    /** @var Config * */
    public $config;

    /** @var Sign[] * */
    private $signTiles = array();

    public const SIGN_REFRESH_DELAY = 5;
    private $signRefreshTaskHandler;

    /**
     * Init the arenas
     * @param Config $config
     */
    public function init(Config $config){
        PluginUtils::logOnConsole(TextFormat::GREEN . "Init" . TextFormat::RED . " ArenaManager");
        $this->server = Server::getInstance();
        $this->config = $config;

        if(!$this->config->get("arenas")){
            $this->config->set("arenas", []);
            $arenaPositions = [];
        }else{
            $arenaPositions = $this->config->get("arenas");
        }

        if(!$this->config->signs){
            $this->config->set("signs", []);
            $signPositions = [];
        }else{
            $signPositions = $this->config->signs;
        }

        // Load arenas and signs
        $this->parseArenaPositions($arenaPositions);
        $this->parseSignPositions($signPositions);

        // Launch sign refreshing task
        $task = new SignRefreshTask(OneVsOne::getInstance());
        $task->arenaManager = $this;
        $this->signRefreshTaskHandler = OneVsOne::getInstance()->getScheduler()->scheduleRepeatingTask($task, self::SIGN_REFRESH_DELAY * 20);
    }

    /**
     * Create arenas
     * @param array $arenaPositions
     */
    public function parseArenaPositions(array $arenaPositions){
        foreach($arenaPositions as $n => $arenaPosition){
            Server::getInstance()->loadLevel($arenaPosition[2]);
            if(($level = $arenaPosition[2]) === null){
                Server::getInstance()->getLogger()->error("[1vs1] - " . $arenaPosition[2] . " is not loaded. Arena " . $n . " is disabled.");
            }else{
                /* Added support for custom spawnpoints.
                * Probably not the best method of saving/loading positions.
                */
                //TODO: Try to optimize
                $spawn1 = new Location($arenaPosition[0][0], $arenaPosition[0][1], $arenaPosition[0][2], $arenaPosition[0][3], $arenaPosition[0][4], Server::getInstance()->getLevelByName($level));
                $spawn2 = new Location($arenaPosition[1][0], $arenaPosition[1][1], $arenaPosition[1][2], $arenaPosition[1][3], $arenaPosition[1][4], Server::getInstance()->getLevelByName($level));
                $newArena = new Arena($spawn1, $spawn2, $this);
                array_push($this->arenas, $newArena);
                /* Do we really need all this debug stuff? */
                $this->getServer()->getLogger()->debug("[1vs1] - Arena " . $n . " loaded!");
                //TODO: Debug the locations of arena.

            }
        }
    }

    /**
     * Load signs
     * @param array $signPositions
     */
    public function parseSignPositions(array $signPositions){
        PluginUtils::logOnConsole(TextFormat::GREEN . "Loaded " . TextFormat::RED . count($signPositions) . " signs");
        foreach($signPositions as $n => $signPosition){
            Server::getInstance()->loadLevel($signPosition[3]);
            if(($level = Server::getInstance()->getLevelByName($signPosition[3])) !== null){
                $newSignPosition = new Position($signPosition[0], $signPosition[1], $signPosition[2], $level);
                /** @var Sign $tile*/
                $tile = $level->getTile($newSignPosition);
                if($tile !== null){
                    $cleanTileTitle = TextFormat::clean($tile->getText()[0]);
                    $cleanOnevsOneTitle = TextFormat::clean(OneVsOne::SIGN_TITLE);

                    // Load it only if it's a sign with OneVsOne title
                    if($tile !== null && $tile instanceof Sign && $cleanTileTitle === $cleanOnevsOneTitle){
                        array_push($this->signTiles, $tile);
                        continue;
                    }
                }
            }else{
                PluginUtils::logOnConsole(TextFormat::RED . "Level " . $signPosition[3] . " does not exist. Please check configuration.");
            }
        }
    }

    public function getServer(): Server{
        return $this->server;
    }

    /**
     * Add player into the queue
     * @param Player $newPlayer
     * @return bool
     */
    public function addNewPlayerToQueue(Player $newPlayer) : bool{

        // Check that player is not already in the queue
        if(in_array($newPlayer, $this->queue)){
            PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_alreadyinqueue"));
            return false;
        }

        // Check that player is not currently in an arena
        $currentArena = $this->getPlayerArena($newPlayer);
        if($currentArena != null){
            PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("arena_alreadyinarena"));
            return false;
        }

        // add player to queue
        array_push($this->queue, $newPlayer);

        // display some stats
        PluginUtils::logOnConsole("[1vs1] - There is actually " . count($this->queue) . " players in the queue");
        PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_join"));
        PluginUtils::sendDefaultMessage($newPlayer, OneVsOne::getMessage("queue_playersinqueue") . count($this->queue));
        $newPlayer->sendTip(OneVsOne::getMessage("queue_popup"));

        $this->launchNewRounds();
        $this->refreshSigns();
        return true;
    }

    /**
     * Launches new rounds if necessary
     */
    private function launchNewRounds(){

        // Check that there is at least 2 players in the queue
        if(count($this->queue) < 2){
            Server::getInstance()->getLogger()->debug("There is not enough players to start a duel : " . count($this->queue));
            return false;
        }

        // Check if there is any arena free (not active)
        Server::getInstance()->getLogger()->debug("Checking " . count($this->arenas) . " arenas");

        $freeArena = null;
        $freeArenaCount = 0;
        foreach($this->arenas as $arena){
            if(!$arena->active){
                $freeArenaCount++;
                $freeArena[$freeArenaCount] = $arena;
            }
        }

        if($freeArena == null){
            Server::getInstance()->getLogger()->debug(OneVsOne::getMessage("pluginprefix") . OneVsOne::getMessage("no_freearena"));
            return false;
        }

        //Randomize
        $freeArenas = count($freeArena);
        $finalArena = mt_rand(1, $freeArenas);
        /** @var Arena $freeArenafinal */
        $freeArenafinal = $freeArena[$finalArena];
        // Send the players into the arena (and remove them from queues)
        $roundPlayers = array();
        array_push($roundPlayers, array_shift($this->queue), array_shift($this->queue));
        Server::getInstance()->getLogger()->debug("[1vs1] - Starting duel : " . $roundPlayers[0]->getName() . " vs " . $roundPlayers[1]->getName());
        $freeArenafinal->startRound($roundPlayers);
        return true;

    }

    /**
     * Allows to be notify when rounds ends
     * @param Arena $arena
     *
     *  WHAT HAVE YOU GUYS DONE
     *  WHAT IS THIS
     *  //FIXME
     */
    public function notifyEndOfRound(Arena $arena){
        $this->launchNewRounds();
    }

    /**
     * Get current arena for player
     * @param Player $player
     * @return Arena|null
     */
    public function getPlayerArena(Player $player): ?Arena{
        foreach($this->arenas as $arena){
            if($arena->isPlayerInArena($player)){
                return $arena;
            }
        }
        return null;
    }

    /**
     * Reference a new arena at this location
     * @param Location $spawn1
     * @param Location $spawn2
     */
    public function referenceNewArena(Location $spawn1, Location $spawn2){
        // Create a new arena
        $newArena = new Arena($spawn1, $spawn2, $this);

        // Add it to the array
        array_push($this->arenas, $newArena);

        // Save it to config
        $arenas = $this->config->arenas;
        array_push($arenas, [[$spawn1->getX(), $spawn1->getY(), $spawn1->getZ(), $spawn1->getYaw(), $spawn1->getPitch()], [$spawn2->getX(), $spawn2->getY(), $spawn2->getZ(), $spawn2->getYaw(), $spawn2->getPitch()], $spawn1->getLevel()->getName()]);
        $this->config->set("arenas", $arenas);
        $this->config->save();

        $this->config->set("arenas", $arenas);
        $this->config->save();
    }

    /**
     * Remove a player from queue
     * @param Player $player
     * @return bool
     */
    public function removePlayerFromQueueOrArena(Player $player) : bool{
        $currentArena = $this->getPlayerArena($player);
        if($currentArena != null){
            $currentArena->onPlayerDeath($player);
            return true;
        }

        $index = array_search($player, $this->queue);
        if($index != -1){
            unset($this->queue[$index]);
        }
        $this->refreshSigns();
        return true;
    }

    public function getNumberOfArenas(){
        return count($this->arenas);
    }

    public function getNumberOfFreeArenas(){
        $numberOfFreeArenas = count($this->arenas);
        foreach($this->arenas as $arena){
            if($arena->active){
                $numberOfFreeArenas--;
            }
        }
        return $numberOfFreeArenas;
    }

    public function getNumberOfPlayersInQueue(){
        return count($this->queue);
    }

    /**
     * Add a new 1vs1 sign
     * @param Sign $signTile
     */
    public function addSign(Sign $signTile){
        $signs = $this->config->signs;
        $signs[count($this->signTiles)] = [$signTile->getX(), $signTile->getY(), $signTile->getZ(), $signTile->getLevel()->getName()];
        $this->config->set("signs", $signs);
        $this->config->save();
        array_push($this->signTiles, $signTile);
    }

    /**
     * Refresh all 1vs1 signs
     */
    public function refreshSigns(){
        foreach($this->signTiles as $signTile){
            if($signTile->level != null){
                $signTile->setText(OneVsOne::SIGN_TITLE, "-Waiting " . $this->getNumberOfPlayersInQueue(), "-Arenas: " . $this->getNumberOfFreeArenas(), "-+===+-");
            }
        }
    }
}



