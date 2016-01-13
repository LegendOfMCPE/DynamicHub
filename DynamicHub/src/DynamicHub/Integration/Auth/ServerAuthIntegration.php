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
use ServerAuth\Events\ServerAuthAuthenticateEvent;
use ServerAuth\ServerAuth;

class ServerAuthIntegration implements AuthIntegration{
	private $hub;
	private $sa;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
		$this->sa = $hub->getServer()->getPluginManager()->getPlugin("ServerAuth");
		if(!($this->sa instanceof ServerAuth)){
			throw new \RuntimeException("ServerAuth is not loaded");
		}
		$hub->getServer()->getPluginManager()->registerEvents($this, $hub);
	}

	public function onAuth(ServerAuthAuthenticateEvent $event){
		$this->hub->onPlayerAuth($event->getPlayer());
	}
}
