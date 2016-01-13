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

class MatchUser{
	/** @type Gamer */
	private $gamer;

	/** @type Match|null */
	private $currentMatch = null;
	/** @type bool */
	private $isSpectator;

	public function __construct(Gamer $gamer){
		$this->gamer = $gamer;
	}

	/**
	 * @param bool &$isSpectator
	 *
	 * @return Match|null
	 */
	public function getCurrentMatch(&$isSpectator = false){
		$isSpectator = $this->isSpectator;
		return $this->currentMatch;
	}

	/**
	 * @internal Warning: Do not call this method directly. It should only be called from the Match class remove*** or
	 *           add*** methods.
	 *
	 * @param Match|null $currentMatch
	 * @param bool       $isSpectator
	 */
	public function setCurrentMatch($currentMatch, $isSpectator = false){
		$this->currentMatch = $currentMatch;
		$this->isSpectator = $isSpectator;
	}

	/**
	 * @internal do not trigger this method directly! Use $gamer->$name(...$arguments) instead!
	 *
	 * @param string  $name
	 * @param mixed[] $arguments
	 */
	public function __call($name, $arguments){
		$callable = [$this, $name];
		if(is_callable($callable)){
			$callable(...$arguments);
		}else{
			$callable[0] = $this->gamer;
			if(is_callable($callable)){
				$callable(...$arguments);
			}else{
				throw new \BadMethodCallException("Method does not exist in " . get_class($this) . " nor in " . Gamer::class . "!");
			}
		}
	}

	public function onQuitGame(){
		// TODO handle match stuff
		if($this->currentMatch !== null){
			$method = $this->isSpectator ? "removeSpectator" : "removePlayer";
			$this->currentMatch->$method($this->getGamer());
		}
	}

	/**
	 * @return Gamer
	 */
	public function getGamer() : Gamer{
		return $this->gamer;
	}
}
