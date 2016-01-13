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

use DynamicHub\Utils\Translatable;

class Ballot{
	const VOTE_SUCCESS = 0;
	const VOTE_MODIFIED = 1;
	const VOTE_DUPLICATED = 2;
	const VOTE_UNKNOWN_CHOICE = 3;

	/** @type Translatable */
	private $name;
	/** @type int[][] */
	private $choices = [];

	/**
	 * Ballot constructor.
	 *
	 * @param Translatable $name
	 * @param string[]     $choices
	 * @param bool         $shuffle
	 */
	public function __construct(Translatable $name, array $choices, bool $shuffle = true){
		if($choices < 1){
			throw new \InvalidArgumentException("A ballot must have at least one choice");
		}
		if($shuffle){
			shuffle($choices);
		}
		foreach($choices as $choice){
			$this->choices[$choice] = [];
		}
		$this->name = $name;
	}

	public function vote(string $electorName, string $desired, int $ratio) : int{
		if(!isset($this->choices[$desired])){
			return self::VOTE_UNKNOWN_CHOICE;
		}
		$rm = false;
		foreach($this->choices as $choice => &$supporters){
			if(isset($supporters[$electorName])){
				if($choice === $desired){
					return self::VOTE_DUPLICATED;
				}
				$rm = true;
				unset($supporters[$electorName]);
			}
		}
		$this->choices[$desired][$electorName] = $ratio;
		$this->onChanged();
		return $rm ? self::VOTE_MODIFIED : self::VOTE_SUCCESS;
	}

	protected function onChanged(){
		// TODO Implement changeHook() method.
	}

	public function getDecision() : string{
		$maxKey = null;
		$maxValue = -1;

		foreach($this->choices as $choice => $supporters){
			$value = array_sum($supporters);
			if($value > $maxValue){ // don't worry about cases where they are the same - the order of choices array can be shuffled
				$maxKey = $choice;
				$maxValue = $value;
			}
		}

		return $maxKey;
	}

	public function countVotes() : int{
		return array_sum(array_map("array_sum", $this->choices));
	}

	public function getName() : Translatable{
		return $this->name;
	}
}
