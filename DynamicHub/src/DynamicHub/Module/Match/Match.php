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

namespace DynamicHub\Module\Match;

use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Match\MapProvider\CopyMapTask;
use DynamicHub\Module\Match\MapProvider\DeleteMapTask;
use DynamicHub\Module\Match\MapProvider\ThreadedMapProvider;
use DynamicHub\Module\Match\Vote\Ballot;
use DynamicHub\Module\Match\Vote\VoteTable;
use DynamicHub\Utils\StaticTranslatable;
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
	/** @type MatchUser[] */
	private $players = [], $spectators = [];

	/** @type int in half-seconds */
	private $startTimer = null, $prepTimer = null, $loadTimer = null;
	/** @type VoteTable */
	private $voteTable;
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

	/////////////
	// getters //
	/////////////
	public function getGame() : MatchBasedGame{
		return $this->game;
	}

	public function getMatchId() : int{
		return $this->matchId;
	}

	public function getState() : int{
		return $this->state;
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

	///////////////////////
	// public action API //
	///////////////////////
	public function addPlayer(Gamer $gamer, int &$fault = MatchJoinFault::SUCCESS) : bool{
		$config = $this->getMatchBaseConfig();

		// prerequisites
		if($this->state !== MatchState::OPEN){
			$fault = MatchJoinFault::CLOSED;
			return false;
		}
		if($gamer->getModule() !== $this->game){
			$fault = MatchJoinFault::NOT_IN_GAME;
			return false;
		}
		$user = $this->getGame()->getMatchUsers($gamer);
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
		$this->players[$gamer->getId()] = $user;
		$gamer->getPlayer()->teleport($config->getNextPlayerJoinPosition());
		/** @noinspection PhpInternalEntityUsedInspection */
		$user->setCurrentMatch($this, false);

		// recalculate players
		$count = count($this->players);
		if($count >= $this->getMatchBaseConfig()->minPlayers){ // we can start the timer now
			$this->startTimer = $config->maxWaitTime * 2;
		}elseif($count >= $config->maxPlayers){
			$this->startTimer = $config->minWaitTime * 2;
		}elseif($this->startTimer < $config->minWaitTime){
			$this->startTimer = $config->minWaitTime * 2;
		}

		$gamer->getPlayer()->teleport($this->getMatchBaseConfig()->getNextPlayerJoinPosition());
		foreach($this->players as $user){
			$player = $user->getGamer();
			$player->addExVis($gamer->getPlayer());
			$gamer->addExVis($player->getPlayer());
		}
		foreach($this->spectators as $user){
			$user->getGamer()->addExVis($gamer->getPlayer());
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

		$this->spectators[$gamer->getId()] = $user = $this->getGame()->getMatchUsers($gamer);
		/** @noinspection PhpInternalEntityUsedInspection */
		$user->setCurrentMatch($this, true);
		$gamer->getPlayer()->teleport($this->getMatchBaseConfig()->getNextSpectatorJoinPosition());
		foreach($this->players as $player){
			$player->getGamer()->addExVis($gamer->getPlayer());
		}
		return true;
	}

	public function removePlayer(Gamer $gamer) : bool{
		if(!isset($this->players[$gamer->getId()])){
			return false;
		}
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->players[$gamer->getId()]->setCurrentMatch(null);
		unset($this->players[$gamer->getId()]);
		if($this->state === MatchState::OPEN){
			$config = $this->getMatchBaseConfig();
			if(count($this->players) < $config->minPlayers){
				$this->startTimer = null;
			}
			foreach($this->players as $player){
				$player->getGamer()->removeExVis($gamer->getPlayer());
				$gamer->removeExVis($player->getGamer()->getPlayer());
			}
			foreach($this->spectators as $spectator){
				$gamer->removeExVis($spectator->getGamer()->getPlayer());
			}
		}
		return true;
	}

	public function removeSpectator(Gamer $gamer) : bool{
		if(isset($this->spectators[$gamer->getId()])){
			/** @noinspection PhpInternalEntityUsedInspection */
			$this->spectators[$gamer->getId()]->setCurrentMatch(null);
			unset($this->spectators[$gamer->getId()]);

			foreach($this->players as $player){
				$player->getGamer()->removeExVis($gamer->getPlayer());
			}
			return true;
		}
		return false;
	}

	////////////////////
	// tick functions //
	////////////////////
	public function halfSecondTick(){
		if($this->state === MatchState::OPEN){
			$this->tickOpen();
		}elseif($this->state === MatchState::PREPARING){
			$this->tickPrepare();
		}
	}

	protected function tickOpen(){
		if($this->startTimer !== null){
			$this->startTimer--;
			if($this->startTimer <= 0){
				$this->changeStateToPreparing();
			}
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
					$this->loadTimer = $this->prepResult->getMapProvider()->getLoadSeconds() * 2;
					$this->onLoadStart();
				}else{
					// TODO invalid level!
				}
			}
		}
	}

	/////////////////////////////////
	// override/abstract functions //
	/////////////////////////////////
	/**
	 * Triggered when RUNNING has ended to release resources related to the level
	 */
	protected function releaseLevelResources(){
		$copy = $this->players;
		foreach($copy as $player){
			$this->removePlayer($player->getGamer());
			$player->getGamer()->getPlayer()->teleport($this->getGame()->getSpawn());
		}
		$copy = $this->spectators;
		foreach($copy as $spectator){
			$this->removeSpectator($spectator->getGamer());
			$spectator->getGamer()->getPlayer()->teleport($this->getGame()->getSpawn());
		}
		$server = $this->getGame()->getHub()->getServer();
		$server->unloadLevel($this->getLevel());
		$server->getScheduler()->scheduleAsyncTask(new DeleteMapTask($this->levelDir));
	}

	/**
	 * Returns the initial configuration for <b>this</b> match.
	 *
	 * @return MatchBaseConfig
	 */
	public abstract function getMatchBaseConfig() : MatchBaseConfig;

	protected function toPrepResult(VoteTable $table) : MatchPrepResult{
		$result = new MatchPrepResult;
		$result->setMapProvider($this->getMapProviderByName($table->getBallot("map")->getDecision()));
	}

	protected abstract function getMapProviderByName(string $name) : ThreadedMapProvider;

	/**
	 * @return string[]
	 */
	public abstract function getMapChoices() : array;

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

	//////////////////////
	// state transition //
	//////////////////////
	public function changeStateToPreparing(){
		$this->state = MatchState::PREPARING;
		$this->prepTimer = $this->getMatchBaseConfig()->maxPrepTime * 2;
		$this->voteTable = new VoteTable($this);
		$this->voteTable->addBallot(new Ballot(new StaticTranslatable("map"), $this->getMapChoices()));
	}

	public function changeStateToLoading(){
		$this->prepResult = $this->toPrepResult($this->voteTable);
		$server = $this->getGame()->getHub()->getServer();
		$server->getScheduler()->scheduleAsyncTask(new CopyMapTask($this->prepResult->getMapProvider(), $this));
		$this->state = MatchState::LOADING;
	}

	public function onMapLoaded(string $dir){
		$this->levelDir = $dir;
	}

	protected function onLoadStart(){
		$iterator = new \ArrayIterator($this->prepResult->getMapProvider()->getPlayerLoadPos());
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
		$rand = $this->prepResult->getMapProvider()->getSpectatorLoadPos();
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

	public function garbage(){
		$this->state = MatchState::GARBAGE;
	}

	public function onMove(PlayerMoveEvent $event, /** @noinspection PhpUnusedParameterInspection */
	                       Gamer $gamer){
		$from = $event->getFrom();
		$to = $event->getTo();
		if($this->state === MatchState::LOADING and !(new Vector3($to->x, $to->y, $to->z))->equals($from)){
			$event->setCancelled();
		}
	}
}
