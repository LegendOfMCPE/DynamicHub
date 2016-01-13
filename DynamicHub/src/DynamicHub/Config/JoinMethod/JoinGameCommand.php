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
use DynamicHub\Gamer\Gamer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;

class JoinGameCommand extends Command implements PluginIdentifiableCommand{
	private $hub;
	private $gameName;

	public function __construct(DynamicHub $hub, string $name, string $gameName, array $aliases){
		parent::__construct($name, "Join $gameName", "/$name", $aliases);
		$this->hub = $hub;
		$this->gameName = $gameName;
		$hub->getServer()->getCommandMap()->register("join", $this);
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		$module = $this->hub->getModule($this->gameName);
		if(!($sender instanceof Player)){
			$sender->sendMessage("Please run this command in-game");
			return true;
		}
		if(!(($gamer = $this->hub->getGamerForPlayer($sender)) instanceof Gamer)){
			$sender->sendMessage("Please wait while your account is being loaded.");
			return true;
		}
		$gamer->setModule($module);
		return true;
	}

	public function getPlugin(){
		return $this->hub;
	}
}
