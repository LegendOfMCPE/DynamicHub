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

use DynamicHub\Gamer\Gamer;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class StandardListener implements Listener{
	private $hub;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
		$hub->getServer()->getPluginManager()->registerEvents($this, $hub);
	}

	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority MONITOR
	 */
	public function onQuit(PlayerQuitEvent $event){
		$this->hub->onPlayerQuit($event->getPlayer());
	}

	/**
	 * @param PlayerChatEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function onChat(PlayerChatEvent $event){
		$gamer = $this->hub->getGamerForPlayer($event->getPlayer());
		if($gamer !== null){
			$module = $gamer->getModule();
			if($module === null){
				$event->setCancelled();
				$gamer->getPlayer()->sendMessage("You cannot chat until your account is loaded!"); // TODO translate
				return;
			}
			$event->setRecipients(array_filter($event->getRecipients(), function(Player $player) use ($module){
				$gamer = $this->hub->getGamerForPlayer($player);
				return $gamer instanceof Gamer and $gamer->getModule() === $module;
			}));
		}
	}

	public function onLvChange(EntityLevelChangeEvent $event){
		// TODO handle visibility stuff
	}
}
