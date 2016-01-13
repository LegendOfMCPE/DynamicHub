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

namespace DynamicHub\Module\Match\Vote;

use DynamicHub\Module\Match\Match;

class VoteTable{
	/** @type Match */
	private $match;

	/** @type Ballot[] */
	private $ballots = [];

	public function __construct(Match $match){
		$this->match = $match;
	}

	public function addBallot(Ballot $ballot){
		$this->ballots[$ballot->getName()->get()] = $ballot;
	}

	/**
	 * @param string $name
	 *
	 * @return Ballot|null
	 */
	public function getBallot(string $name){
		return $this->ballots[$name] ?? null;
	}

	public function getBallots(){
		return $this->ballots;
	}
}
