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

class MatchBaseConfig{
	// players
	public $maxPlayers;
	public $semiMaxPlayers;
	public $minPlayers;

	// time, in seconds
	public $minWaitTime;
	public $maxWaitTime;
	public $maxMatchTime;
	public $maxPrepTime;

	// positions
	public $playerJoinPositions = [];
	public $spectatorJoinPositions = [];

	public function getNextPlayerJoinPosition(){
		if(next($this->playerJoinPositions) === false){
			reset($this->playerJoinPositions);
		}
		return current($this->playerJoinPositions);
	}

	public function getNextSpectatorJoinPosition(){
		if(next($this->spectatorJoinPositions) === false){
			reset($this->spectatorJoinPositions);
		}
		return current($this->spectatorJoinPositions);
	}
}
