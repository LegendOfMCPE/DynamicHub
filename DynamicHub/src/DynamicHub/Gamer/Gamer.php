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

namespace DynamicHub\Gamer;

use DynamicHub\DynamicHub;
use DynamicHub\Module\Module;
use pocketmine\Player;

class Gamer{
	private $hub;
	private $player;
	/** @type Module|null */
	private $module = null;
	/** @type GamerData */
	private $data = null;

	/** @type GamerStatus */
	private $status = null;

	/** @type bool */
	private $defaultVisible = true;
	/** @type bool[] keys as entity IDs of players who exceptionally can/cannot see this player, values as boolean true */
	private $visEx = [];

	public function __construct(DynamicHub $hub, Player $player){
		$this->hub = $hub;
		$this->player = $player;
		$player->blocked = true;
		// TODO load data
		$player->sendMessage("Loading account data for you. Please wait..."); // TODO translate
		$this->setDefaultVisible(false);
	}

	public function onDataLoaded(GamerData $data){
		$this->data = $data;
		$this->player->sendMessage("Your account has been loaded."); // TODO translate
		$this->player->blocked = false;
		$lastModule = $this->hub->getModule($data->lastModule);
		if($lastModule === null){
			$lastModule = $this->hub->getHubModule();
		}
		$this->module = $lastModule;
		$lastModule->onJoin($this);
	}

	public function setModule(Module $module) : bool{
		if($this->module === null){
			return false;
		}
		$this->module->quit($this);
		$this->module = $module;
		$module->join($this);
		return true;
	}

	public function onQuit(){
		if($this->module !== null){
			$this->module->quit($this);
			$this->saveData();
		}
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getModule(){
		return $this->module;
	}

	public function getData() : GamerData{
		return $this->data;
	}

	public function saveData(){
	}

	public function getId() : int{
		return $this->player->getId();
	}

	public function halfSecondTick(){
		$this->getPlayer()->sendTip($this->status->get()); // TODO translation support
	}

	public function isDefaultVisible() : bool{
		return $this->defaultVisible;
	}

	public function setDefaultVisible(bool $vis) : bool{
		if($vis !== $this->defaultVisible){
			$this->defaultVisible = $vis;
			if($vis){ // was invisible, now visible
				foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
					if(!isset($this->visEx[$player->getId()])){
						$player->showPlayer($this->getPlayer());
					}
				}
			}else{ // was visible, now invisible
				foreach($this->getPlayer()->getLevel()->getPlayers() as $player){
					if(!isset($this->visEx[$player->getId()])){
						$player->hidePlayer($this->getPlayer());
					}
				}
			}
			$this->visEx = [];
		}
	}

	/**
	 * Adds a player who can see this player when this player is default invisible, or cannot see this player when this
	 * player is default visible
	 *
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function addExVis(Player $player) : bool{
		if($player === $this->getPlayer()){
			return false;
		}
		if(isset($this->visEx[$player->getId()])){
			return false;
		}
		$this->visEx[$player->getId()] = true;
		$method = $this->defaultVisible ? "hidePlayer" : "showPlayer";
		$player->$method($this->getPlayer());
		return true;
	}

	public function removeExVis(Player $player) : bool{
		if($player === $this->getPlayer()){
			return false;
		}
		if(!isset($this->visEx[$player->getId()])){
			return false;
		}
		unset($this->visEx[$player->getId()]);
		$method = $this->defaultVisible ? "showPlayer" : "hidePlayer";
		$player->$method($this->getPlayer());
		return true;
	}
}
