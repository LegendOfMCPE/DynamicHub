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

use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Module;
use pocketmine\event\Event;
use pocketmine\event\Listener;

class RegisteredGameEventHandler implements Listener{
	private $module;
	private $listener;
	private $method;

	public function __construct(Module $module, Listener $listener, string $method){
		$this->module = $module;
		$this->listener = $listener;
		$this->method = $method;
	}

	public function execute(Event $event, Gamer $gamer){
		$this->listener->{$this->method}($event, $gamer);
	}

	public function getModule(){
		return $this->module;
	}

	public function getListener(){
		return $this->listener;
	}

	public function getMethod(){
		return $this->method;
	}
}
