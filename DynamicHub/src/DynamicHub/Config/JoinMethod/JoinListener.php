<?php

/*
 * DynamicHub
 *
 * Copyright (C) 2015-2016 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace DynamicHub\Config\JoinMethod;

use DynamicHub\DynamicHub;
use pocketmine\block\SignPost;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\tile\Sign;

class JoinListener implements Listener{
	private $hub;

	public function __construct(DynamicHub $hub){
		if($hub->isSingle()){
			return;
		}
		$this->hub = $hub;
		$hub->getServer()->getPluginManager()->registerEvents($this, $hub);
	}

	/**
	 * @param PlayerMoveEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$gamer = $this->hub->getGamerForPlayer($player);
		$delta = $event->getTo()->subtract($event->getFrom());
		$bb = clone $player->getBoundingBox();
		$bb->offset($delta->x, $delta->y, $delta->z);
		foreach($this->hub->getJoinMethods() as $method){
			if(!$method->isLevelCorrect($player->getLevel())){
				continue;
			}
			if($method instanceof PortalJoinMethod){
				if($method->isLevelCorrect($player->getLevel()) and $method->bb->intersectsWith($bb)){
					$gamer->setModule($this->hub->getModule($method->target));
					return;
				}
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority LOW
	 */
	public function onInteract(PlayerInteractEvent $event){
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			return;
		}
		$player = $event->getPlayer();
		$gamer = $this->hub->getGamerForPlayer($player);
		$item = $event->getItem();
		$block = $event->getBlock();
		if($block instanceof SignPost){
			$sign = $block->getLevel()->getTile($block);
			if(!($sign instanceof Sign)){
				unset($sign);
			}
		}
		foreach($this->hub->getJoinMethods() as $method){
			if(!$method->isLevelCorrect($player->getLevel())){
				continue;
			}
			if($method instanceof KeyJoinMethod){
				if($method->key === null or $method->key->equals($item, true, false)){
					if($method->lock === null or $method->lock->equals($block)){
						$gamer->setModule($this->hub->getModule($method->target));
						return;
					}
				}
			}elseif(isset($sign) and $method instanceof SignJoinMethod){
				if($method->matches($sign)){
					$gamer->setModule($this->hub->getModule($method->target));
					return;
				}
			}
		}
	}
}
