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

use DynamicHub\Module\Match\MapProvider\ThreadedMapProvider;
use pocketmine\math\Vector3;

class MatchPrepResult{
	/** @type string */
	private $mapName;
	/** @type ThreadedMapProvider */
	private $mapProvider;
	/** @type int */
	private $loadSeconds;
	/**
	 * @type Vector3[] $playerLoadPos players will be teleported to the positions in the array in the same order as in
	 *       the array (e.g. if only 2 players joined, only the first two positions will be selected)
	 */
	private $playerLoadPos = [];
	/**
	 * @type Vector3[] $spectatorLoadPos (different or random) positions from the array will be selected to teleport
	 *       spectators to
	 */
	private $spectatorLoadPos = [];

	public function getMapProvider() : ThreadedMapProvider{
		return $this->mapProvider;
	}

	public function setMapProvider(ThreadedMapProvider $mapProvider) : MatchPrepResult{
		$this->mapProvider = $mapProvider;
		return $this;
	}

	public function getMapName() : string{
		return $this->mapName;
	}

	public function setMapName(string $mapName) : MatchPrepResult{
		$this->mapName = $mapName;
		return $this;
	}

	public function getLoadSeconds() : int{
		return $this->loadSeconds;
	}

	public function setLoadSeconds(int $secs) : MatchPrepResult{
		$this->loadSeconds = $secs;
		return $this;
	}

	/**
	 * @return Vector3[]
	 */
	public function getPlayerLoadPos() : array{
		return $this->playerLoadPos;
	}

	/**
	 * @param Vector3[] $playerLoadPos
	 *
	 * @return MatchPrepResult
	 */
	public function setPlayerLoadPos(array $playerLoadPos) : MatchPrepResult{
		$this->playerLoadPos = $playerLoadPos;
		return $this;
	}

	/**
	 * @return Vector3[]
	 */
	public function getSpectatorLoadPos() : array{
		return $this->spectatorLoadPos;
	}

	/**
	 * @param Vector3[] $spectatorLoadPos
	 *
	 * @return MatchPrepResult
	 */
	public function setSpectatorLoadPos(array $spectatorLoadPos) : MatchPrepResult{
		$this->spectatorLoadPos = $spectatorLoadPos;
		return $this;
	}
}
