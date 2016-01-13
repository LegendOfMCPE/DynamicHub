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

use DynamicHub\DataProvider\NextIdFetchedCallback;
use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Game;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\Position;

abstract class MatchBasedGame extends Game implements NextIdFetchedCallback, Listener{
	/** @type Match[] */
	private $matches = [];
	/** @type MatchUser[] */
	private $matchUsers = [];

	public function halfSecondTick(){
		parent::halfSecondTick();
		$count = 0;
		foreach($this->matches as $match){
			if($match->getState() === MatchState::OPEN){
				$count++;
			}
		}
		if($count < $this->getMinOpenGames()){
			$this->getHub()->getDataProvider()->fetchNextId($this);
		}

		foreach($this->matches as $match){
			$match->halfSecondTick();
		}
	}

	public function canStartNewMatch(){
		$running = 0;
		foreach($this->matches as $match){
			if(
				$match->getState() !== MatchState::OPEN and
				$match->getState() !== MatchState::PREPARING and
				$match->getState() !== MatchState::LOADING and
				$match->getState() !== MatchState::GARBAGE
			){
				$running++;
			}
		}
		return $running < $this->getMaxRunningGames();
	}

	public function getMatches(){
		return $this->matches;
	}

	public function getMatchById(int $id){
		return $this->matches[$id] ?? null;
	}

	public abstract function getMinOpenGames() : int;

	public abstract function getMaxRunningGames() : int;

	public function onNextIdFetched(int $nextId){
		$this->matches[$nextId] = $this->newMatch($nextId);
	}

	public abstract function newMatch(int $matchId) : Match;

	public function newMatchUser(Gamer $gamer) : MatchUser{
		return new MatchUser($gamer);
	}

	public function onJoin(Gamer $gamer){
		$gamer->setDefaultVisible(false);
		$this->matchUsers[$gamer->getId()] = $this->newMatchUser($gamer);
	}

	public function onQuit(Gamer $gamer){
		if(isset($this->matchUsers[$gamer->getId()])){
			$this->matchUsers[$gamer->getId()]->onQuitGame();
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 * @param Gamer           $gamer
	 *
	 * @priority        LOW
	 * @ignoreCancelled true
	 */
	public function onMove(PlayerMoveEvent $event, Gamer $gamer){
		if(isset($this->matchUsers[$gamer->getId()])){ // this check should actually be redundant
			$mg = $this->matchUsers[$gamer->getId()];
			if($mg->getCurrentMatch() !== null){
				$mg->getCurrentMatch()->onMove($event, $gamer);
			}
		}
	}

	/**
	 * If the game overrides {@link Match::sendPlayersToSpawn()}, no need to implement this function properly, because
	 * this won't get called.
	 */
	public abstract function getSpawn() : Position;

	public function getMatchUsers(Gamer $gamer){
		return $this->matchUsers[$gamer->getId()] ?? null;
	}
}
