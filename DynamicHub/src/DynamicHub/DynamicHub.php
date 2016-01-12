<?php

/*
 * DynamicHub
 *
 * Copyright (C) 2015 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace DynamicHub;

use DynamicHub\Config\JoinMethod\JoinListener;
use DynamicHub\Config\JoinMethod\JoinMethod;
use DynamicHub\DataProvider\DataProvider;
use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Event\GameEventListener;
use DynamicHub\Module\Event\RegisteredGameEventHandler;
use DynamicHub\Module\Game;
use DynamicHub\Module\HubModule;
use DynamicHub\Module\Module;
use DynamicHub\Utils\CallbackPluginTask;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class DynamicHub extends PluginBase{
	private static $NAME = "DynamicHub";

	/** @type Game[] */
	private $loadedGames = [];
	/** @type HubModule */
	private $hubModule;
	/** @type Gamer[] */
	private $gamers = [];
	/** @type GameEventListener[] */
	private $listeners = [];
	/** @type DataProvider */
	private $dataProvider;

	/** @type bool */
	private $single;
	/** @type JoinMethod[] */
	private $joinMethods = [];

	public function onLoad(){
		self::$NAME = $this->getDescription()->getName();
	}

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->single = $this->getConfig()->get("single", false);
		if($this->single){
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackPluginTask($this, function(){
				if(count($this->loadedGames) === 0){
					$this->getLogger()->critical("No games loaded");
					$this->getServer()->getPluginManager()->disablePlugin($this);
				}
			}), 1);
		}else{
			foreach($this->joinMethods as $method){
				$m = JoinMethod::get($this, $method);
				if($m !== null){
					$this->joinMethods[] = $m;
				}
			}
			new JoinListener($this);
			$this->hubModule = new HubModule($this);
		}
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackPluginTask($this, array($this, "halfSecondTick")), 10, 10);
	}

	public function onDisable(){
		if($this->dataProvider !== null){
			$this->dataProvider->finalize();
			unset($this->dataProvider);
		}
	}

	/**
	 * Load a {@link Game} into the plugin
	 *
	 * @param Game $game
	 *
	 * @throws \RuntimeException
	 */
	public function loadGame(Game $game){
		$owner = $game->getOwner();
		if(!in_array($this->getDescription()->getName(), $owner->getDescription()->getDepend())){
			throw new \RuntimeException("A Game must be owned by a plugin that depends on DynamicHub");
		}
		if($this->single and count($this->loadedGames) > 0){
			throw new \RuntimeException("Only one Game can be loaded on this server limited in config");
		}
		$this->loadedGames[strtolower($game->getName()->get())] = $game;
		$game->onLoaded($this);
		$this->getLogger()->info("Loaded " . $game->getName());
	}

	public function unloadGame(Game $game){
		// TODO quit players
		// TODO unregister listeners
	}

	public function registerListeners(Module $module, Listener $listener){
		foreach((new \ReflectionClass($listener))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if($method->isStatic()){
				continue;
			}
			$parameters = $method->getParameters();
			if(count($parameters) !== 2){
				continue;
			}
			$eventClass = $parameters[0]->getClass();
			$gamerClass = $parameters[1]->getClass();
			if(!($eventClass instanceof \ReflectionClass) or !($gamerClass instanceof \ReflectionClass)){
				continue;
			}
			if($gamerClass->getName() !== Gamer::class){
				continue;
			}
			if(
				$eventClass->isSubclassOf(PlayerEvent::class) or
				$eventClass->isSubclassOf(EntityEvent::class) or
				$eventClass->isSubclassOf(Event::class) and $eventClass->hasMethod("getPlayer")
			){
				$priority = EventPriority::NORMAL;
				$ignoreCancelled = false;
				if(preg_match("/^[\t ]*\\* @priority[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
					$matches[1] = strtoupper($matches[1]);
					if(defined(EventPriority::class . "::" . $matches[1])){
						$priority = constant(EventPriority::class . "::" . $matches[1]);
					}
				}
				if(preg_match("/^[\t ]*\\* @ignoreCancelled[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
					$matches[1] = strtolower($matches[1]);
					if($matches[1] === "false"){
						$ignoreCancelled = false;
					}elseif($matches[1] === "true"){
						$ignoreCancelled = true;
					}
				}
				$event = $eventClass->getName();
				$reflection = new \ReflectionClass($event);
				if(strpos((string) $reflection->getDocComment(), "@deprecated") !== false and $this->getServer()->getProperty("settings.deprecated-verbose", true)){
					$this->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.plugin.deprecatedEvent", [
						$module->getOwner()->getName(),
						$event,
						get_class($listener) . "->" . $method->getName() . "()",
					]));
				}
				if(!isset($this->listeners[$identifier = GameEventListener::identifier($event, $priority, $ignoreCancelled)])){
					$this->listeners[$identifier] = new GameEventListener($this, $event, $priority, $ignoreCancelled);
				}
				$this->listeners[$identifier]->addHandler(new RegisteredGameEventHandler($module, $listener, $method->getName()));
			}
		}
	}

	public function onPlayerAuth(Player $player){
		$this->gamers[$player->getId()] = new Gamer($this, $player);
	}

	public function onPlayerQuit(Player $player){
		if(isset($this->gamers[$player->getId()])){
			$this->gamers[$player->getId()]->onQuit();
			unset($this->gamers[$player->getId()]);
		}
	}

	public function halfSecondTick(){
		foreach($this->loadedGames as $game){
			$game->halfSecondTick();
		}
		foreach($this->gamers as $gamer){
			$gamer->halfSecondTick();
		}
	}

	/**
	 * @param string $name
	 * @param bool   $exact
	 *
	 * @return Gamer|null
	 */
	public function getGamerByName(string $name, bool $exact = false){
		$player = ($exact ? $this->getServer()->getPlayerExact($name) : $this->getServer()->getPlayer($name));
		return $player !== null ? $this->getGamerForPlayer($player) : null;
	}

	/**
	 * @param Player $player
	 *
	 * @return Gamer|null
	 */
	public function getGamerForPlayer(Player $player){
		return $this->gamers[$player->getId()] ?? null;
	}

	/**
	 * @param int $id
	 *
	 * @return Gamer|null
	 */
	public function getGamerById(int $id){
		return $this->gamers[$id] ?? null;
	}

	public function getLoadedGame(string $name){
		return $this->loadedGames[$name] ?? null;
	}

	public function isSingle() : bool{
		return $this->single;
	}

	/**
	 * @return JoinMethod[]
	 */
	public function getJoinMethods() : array{
		return $this->joinMethods;
	}

	/**
	 * @param string $name
	 *
	 * @return Module|null
	 */
	public function getModule(string $name){
		$lower = strtolower($name);
		if($lower === strtolower(HubModule::NAME)){
			return $this->hubModule;
		}
		return $this->loadedGames[$lower] ?? null;
	}

	public function getHubModule() : HubModule{
		return $this->hubModule;
	}

	public function getDataProvider() : DataProvider{
		return $this->dataProvider;
	}

	/**
	 * @param Server $server
	 *
	 * @return DynamicHub|null
	 */
	public static function getInstance(Server $server){
		$me = $server->getPluginManager()->getPlugin(self::$NAME);
		if($me instanceof DynamicHub and $me->isEnabled()){
			return $me;
		}
		return null;
	}
}
