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

namespace DynamicHub\Module\Match;

use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Match\MapProvider\ThreadedMapProvider;
use pocketmine\Player;

abstract class Match{
	/** @type MatchBasedGame */
	private $game;
	/** @type int */
	private $matchId;
	/** @type int */
	private $state;
	/** @type Gamer[] */
	private $players = [], $spectators = [];

	/** @type int in half-seconds */
	private $startTimer, $prepTimer;

	protected function __construct(MatchBasedGame $game, int $matchId){
		$this->game = $game;
		$this->matchId = $matchId;
		$this->state = MatchState::OPEN;
	}

	public function getGame() : MatchBasedGame{
		return $this->game;
	}

	public function getMatchId() : int{
		return $this->matchId;
	}

	public function getState() : int{
		return $this->state;
	}

	public function addPlayer(Gamer $gamer, int &$fault = MatchJoinFault::SUCCESS) : bool{
		$config = $this->getMatchConfig();

		// prerequisites
		if($this->state !== MatchState::OPEN){
			$fault = MatchJoinFault::CLOSED;
			return false;
		}
		if($gamer->getModule() !== $this->game){
			$fault = MatchJoinFault::NOT_IN_GAME;
			return false;
		}
		if(!$this->hasJoinPermission($gamer->getPlayer())){
			$fault = MatchJoinFault::NO_PERM;
			return false;
		}
		if(count($this->players) >= $config->maxPlayers){
			$fault = MatchJoinFault::FULL;
			return false;
		}
		if(count($this->players) >= $config->semiMaxPlayers and !$this->hasSemiFullPermission($gamer->getPlayer())){
			$fault = MatchJoinFault::SEMI_FULL;
			return false;
		}

		// add
		$this->players[$gamer->getId()] = $gamer;
		$gamer->getPlayer()->teleport($config->getNextPlayerJoinPosition());

		// recalculate players
		$count = count($this->players);
		if($count >= $this->getMatchConfig()->minPlayers){ // we can start the timer now
			$this->startTimer = $config->maxWaitTime * 2;
		}elseif($count >= $config->maxPlayers){
			$this->startTimer = $config->minWaitTime;
		}elseif($this->startTimer < $config->minWaitTime){
			$this->startTimer = $config->minWaitTime;
		}

		$gamer->getPlayer()->teleport($this->getMatchConfig()->getNextPlayerJoinPosition());

		return true;
	}

	public function addSpectator(Gamer $gamer, int &$fault = MatchJoinFault::SUCCESS) : bool{
		if(!$this->hasSpectatePermission($gamer->getPlayer())){
			$fault = MatchJoinFault::NO_PERM;
			return false;
		}
		if($gamer->getModule() !== $this->game){
			$fault = MatchJoinFault::NOT_IN_GAME;
			return false;
		}
		$this->spectators[$gamer->getId()] = $gamer;
		$gamer->getPlayer()->teleport($this->getMatchConfig()->getNextSpectatorJoinPosition());
		return true;
	}

	public function halfSecondTick(){
		if($this->state === MatchState::OPEN){
			$this->tickOpen();
		}elseif($this->state === MatchState::PREPARING){
			$this->tickPrepare();
		}
	}

	protected function tickOpen(){
		$this->startTimer--;
		if($this->startTimer <= 0){
			if($this->game->canStartNewMatch()){
				$this->changeStateToPreparing();
			}elseif($this->startTimer === 0){
				foreach($this->players as $player){
					$player->getPlayer()->sendMessage("Waiting for an available map..."); // TODO translate
				}
				foreach($this->spectators as $spectator){
					$spectator->getPlayer()->sendMessage("Waiting for an available map...");
				}
			}
		}
	}

	protected function tickPrepare(){
		$this->prepTimer--;
		if($this->prepTimer === 0){
		}
	}

	public function onMapLoaded(string $dir){
		// TODO implement
	}

	public final function hasJoinPermission(Player $player) : bool{
		foreach($this->getJoinPermissions() as $perm){
			if(!$player->hasPermission($perm)){
				return false;
			}
		}
		return true;
	}

	public final function hasSemiFullPermission(Player $player) : bool{
		foreach($this->getSemiFullPermissions() as $perm){
			if(!$player->hasPermission($perm)){
				return false;
			}
		}
		return true;
	}

	public final function hasSpectatePermission(Player $player) : bool{
		foreach($this->getSpectatePermissions() as $perm){
			if(!$player->hasPermission($perm)){
				return false;
			}
		}
		return true;
	}

	/**
	 * Player must have all of these permissions to join this game.
	 * If this method returns an empty array, all players can join.
	 *
	 * @return string[]
	 */
	public function getJoinPermissions() : array{
		return [];
	}

	/**
	 * Player must have all of these permissions to join this game if the game is semi-full.
	 * If this method returns an empty array, all players can join.
	 *
	 * @return string[]
	 */
	public function getSemiFullPermissions() : array{
		return [];
	}

	/**
	 * Player must have all of these permissions to spectate this game.
	 * If this method returns an empty array, all players can join.
	 *
	 * @return string[]
	 */
	public function getSpectatePermissions() : array{
		return [];
	}

	public abstract function getBaseMap() : ThreadedMapProvider;

	/**
	 * Returns the initial configuration for <b>this</b> match.
	 *
	 * @return MatchBaseConfig
	 */
	public abstract function getMatchConfig() : MatchBaseConfig;

	public function changeStateToPreparing(){
		$this->state = MatchState::PREPARING;
		$this->prepTimer = $this->getMatchConfig()->maxPrepTime;
	}

	public function changeStateToLoading(){
		$this->state = MatchState::LOADING;
		// TODO implement function
	}

	public function changeStateToRunning(){
		$this->state = MatchState::RUNNING;
		// TODO implement function
	}

	public function changeStateToFinalizing(){
		$this->state = MatchState::FINALIZING;
		// TODO implement function
	}

	public function garbage(){
		$this->state = MatchState::GARBAGE;
		// TODO implement function
	}

}
