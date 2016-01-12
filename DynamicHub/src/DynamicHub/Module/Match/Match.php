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
use DynamicHub\Module\Match\MapProvider\CopyMapTask;
use DynamicHub\Module\Match\MapProvider\DeleteMapTask;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Match{
	/** @type MatchBasedGame */
	private $game;
	/** @type int */
	private $matchId;
	/** @type int */
	private $state;
	/** @type MatchGamer[] */
	private $players = [], $spectators = [];

	/** @type int in half-seconds */
	private $startTimer = null, $prepTimer = null, $loadTimer = null;
	/** @type MatchPrepResult */
	private $prepResult;
	/** @type string */
	private $levelDir;
	/** @type int */
	private $levelId;

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
		$mg = $this->getGame()->getMatchGamer($gamer);
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
		$this->players[$gamer->getId()] = $mg;
		$gamer->getPlayer()->teleport($config->getNextPlayerJoinPosition());

		// recalculate players
		$count = count($this->players);
		if($count >= $this->getMatchConfig()->minPlayers){ // we can start the timer now
			$this->startTimer = $config->maxWaitTime * 2;
		}elseif($count >= $config->maxPlayers){
			$this->startTimer = $config->minWaitTime * 2;
		}elseif($this->startTimer < $config->minWaitTime){
			$this->startTimer = $config->minWaitTime * 2;
		}

		$gamer->getPlayer()->teleport($this->getMatchConfig()->getNextPlayerJoinPosition());
		foreach($this->players as $mg){
			$player = $mg->getGamer();
			$player->addExVis($gamer->getPlayer());
			$gamer->addExVis($player->getPlayer());
		}
		foreach($this->spectators as $mg){
			$mg->getGamer()->addExVis($gamer->getPlayer());
		}

		return true;
	}

	public function addSpectator(Gamer $gamer, int &$fault = MatchJoinFault::SUCCESS) : bool{
		if($this->state >= MatchState::FINALIZING){
			return MatchJoinFault::CLOSED;
		}
		if(!$this->hasSpectatePermission($gamer->getPlayer())){
			$fault = MatchJoinFault::NO_PERM;
			return false;
		}
		if($gamer->getModule() !== $this->game){
			$fault = MatchJoinFault::NOT_IN_GAME;
			return false;
		}
		$this->spectators[$gamer->getId()] = $this->getGame()->getMatchGamer($gamer);

		$gamer->getPlayer()->teleport($this->getMatchConfig()->getNextSpectatorJoinPosition());
		foreach($this->players as $mg){
			$player = $mg->getGamer();
			$player->addExVis($gamer->getPlayer());
		}
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
			$this->changeStateToPreparing();
		}
	}

	protected function tickPrepare(){
		$this->prepTimer--;
		if($this->prepTimer <= 0){
			$this->changeStateToLoading();
		}
	}

	protected function tickLoad(){
		if($this->loadTimer !== null){
			$this->loadTimer--;
			if($this->loadTimer === 0){
				$this->changeStateToRunning();
			}
		}else{
			$game = $this->getGame();
			$server = $game->getHub()->getServer();
			if(isset($this->levelDir) and $game->canStartNewMatch()){
				if($server->loadLevel($this->levelDir)){
					$level = $server->getLevelByName($this->levelDir);
					$this->levelId = $level->getId();
					$this->loadTimer = $this->prepResult->getLoadSeconds() * 2;
					$this->onLoadStart();
				}else{
					// TODO invalid level!
				}
			}
		}
	}

	public function onMapLoaded(string $dir){
		$this->levelDir = $dir;
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

	protected function sendPlayersToSpawn(){
		foreach($this->players as $gamer){
			$gamer->setCurrentMatch(null);
			$gamer->getGamer()->getPlayer()->teleport($this->getGame()->getSpawn());
		}
		foreach($this->spectators as $gamer){
			$gamer->setCurrentMatch(null);
			$gamer->getGamer()->getPlayer()->teleport($this->getGame()->getSpawn());
		}
		$server =
			$this->getGame()->getHub()->getServer();
		$server->unloadLevel($this->getLevel());
		$server->getScheduler()->scheduleAsyncTask(new DeleteMapTask($this->levelDir));
	}

	/**
	 * Returns the initial configuration for <b>this</b> match.
	 *
	 * @return MatchBaseConfig
	 */
	public abstract function getMatchConfig() : MatchBaseConfig;

	protected abstract function onPreparationTimeout() : MatchPrepResult;

	public function changeStateToPreparing(){
		$this->state = MatchState::PREPARING;
		$this->prepTimer = $this->getMatchConfig()->maxPrepTime * 2;
	}

	public function changeStateToLoading(){
		$this->prepResult = $this->onPreparationTimeout();
		$server = $this->getGame()->getHub()->getServer();
		$server->getScheduler()->scheduleAsyncTask(new CopyMapTask(
			$this->prepResult->getMapProvider(), $this->prepResult->getMapName(), $this));
		$this->state = MatchState::LOADING;
	}

	protected function onLoadStart(){
		$iterator = new \ArrayIterator($this->prepResult->getPlayerLoadPos());
		$level = $this->getLevel();
		foreach($this->players as $player){
			$pos = $iterator->current();
			$yaw = null;
			$pitch = null;
			if($pos instanceof Location){
				$yaw = $pos->yaw;
				$pitch = $pos->pitch;
			}
			$player->getGamer()->getPlayer()->teleport(Position::fromObject($pos, $level), $yaw, $pitch);
			$iterator->next();
		}
		$rand = $this->prepResult->getSpectatorLoadPos();
		foreach($this->spectators as $spectator){
			$pos = $rand[mt_rand(0, count($rand) - 1)];
			$yaw = null;
			$pitch = null;
			if($pos instanceof Location){
				$yaw = $pos->yaw;
				$pitch = $pos->pitch;
			}
			$spectator->getGamer()->getPlayer()->teleport(Position::fromObject($pos, $level), $yaw, $pitch);
		}
	}

	public function changeStateToRunning(){
		$this->state = MatchState::RUNNING;
	}

	public function changeStateToFinalizing(){
		$this->state = MatchState::FINALIZING;
		// TODO implement function

	}

	public function onMove(PlayerMoveEvent $event, /** @noinspection PhpUnusedParameterInspection */
	                       Gamer $gamer){
		$from = $event->getFrom();
		$to = $event->getTo();
		if($this->state === MatchState::LOADING and !(new Vector3($to->x, $to->y, $to->z))->equals($from)){
			$event->setCancelled();
		}
	}

	public function garbage(){
		$this->state = MatchState::GARBAGE;
	}

	/**
	 * @return MatchPrepResult
	 */
	public function getPrepResult(){
		return $this->prepResult;
	}

	public function getLevel(){
		return $this->getGame()->getHub()->getServer()->getLevel($this->levelId);
	}
}
