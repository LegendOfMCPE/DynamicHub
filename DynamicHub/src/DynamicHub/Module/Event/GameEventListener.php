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

namespace DynamicHub\Module\Event;

use DynamicHub\DynamicHub;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;
use pocketmine\plugin\EventExecutor;

class GameEventListener implements EventExecutor, Listener{
	private $hub;
	private $identifier;
	/** @type RegisteredGameEventHandler[]|\SplObjectStorage */
	private $handlers;

	public function __construct(DynamicHub $hub, $event, $priority, $ignoreCancelled){
		$this->hub = $hub;
		$this->identifier = self::identifier($event, $priority, $ignoreCancelled);
		$this->handlers = new \SplObjectStorage;
		$hub->getServer()->getPluginManager()->registerEvent($event, $this, $priority, $this, $hub, $ignoreCancelled);
	}

	public function addHandler(RegisteredGameEventHandler $handler){
		$this->handlers->attach($handler, $handler->getModule());
	}

	public function execute(Listener $listener, Event $event){
		if($event instanceof PlayerEvent){
			$player = $event->getPlayer();
			$gamer = $this->hub->getGamerForPlayer($player);
			if($gamer !== null){
				$module = $gamer->getModule();
			}
		}elseif($event instanceof EntityEvent){
			$entity = $event->getEntity();
			if($entity instanceof Player){
				$gamer = $this->hub->getGamerForPlayer($entity);
				if($gamer !== null){
					$module = $gamer->getModule();
				}
			}
		}else{
			$callable = [$event, "getPlayer"];
			if(is_callable($callable)){
				$player = $callable();
				if($player instanceof Player){
					$gamer = $this->hub->getGamerForPlayer($player);
					if($gamer !== null){
						$module = $gamer->getModule();
					}
				}
			}
		}
		if(isset($module, $gamer)){
			foreach($this->handlers as $handler){
				if($handler->getModule() === $module){
					$handler->execute($event, $gamer);
				}
			}
		}
	}

	public function getIdentifier() : string{
		return $this->identifier;
	}

	public static function identifier(string $event, int $priority, bool $ignoreCancelled) : string{
		return "$event:$priority:" . ($ignoreCancelled ? "1" : "0");
	}
}
