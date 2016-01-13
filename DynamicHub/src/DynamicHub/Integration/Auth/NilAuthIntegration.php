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

namespace DynamicHub\Integration\Auth;

use DynamicHub\DynamicHub;
use pocketmine\event\player\PlayerJoinEvent;

class NilAuthIntegration implements AuthIntegration{
	private $hub;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
		$hub->getServer()->getPluginManager()->registerEvents($this, $hub);
	}

	/**
	 * @param PlayerJoinEvent $event
	 *
	 * @priority MONITOR
	 */
	public function onJoin(PlayerJoinEvent $event){
		$this->hub->onPlayerAuth($event->getPlayer());
	}
}
