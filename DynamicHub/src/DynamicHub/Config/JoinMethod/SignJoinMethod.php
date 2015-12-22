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

namespace DynamicHub\Config\JoinMethod;

use pocketmine\tile\Sign;

class SignJoinMethod extends JoinMethod{
	/** @type int */
	public $matchingLine;
	/** @type string */
	public $matches;

	public function matches(Sign $sign){
		return $sign->getText()[$this->matchingLine - 1] == $this->matches;
	}
}
