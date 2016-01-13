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

namespace DynamicHub\Module\Match\MapProvider;

use pocketmine\math\Vector3;

abstract class ThreadedMapProvider extends \Threaded{
	/** @type string */
	private $name;
	/** @type Vector3[] */
	private $playerLoadPos, $spectatorLoadPos;
	/** @type int */
	private $loadSeconds;

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return Vector3[]
	 */
	public function getPlayerLoadPos() : array{
		return $this->playerLoadPos;
	}

	/**
	 * @return Vector3[]
	 */
	public function getSpectatorLoadPos() : array{
		return $this->spectatorLoadPos;
	}

	public function getLoadSeconds() : int{
		return $this->loadSeconds;
	}

	public abstract function extractTo(string $dir) : bool;
}
