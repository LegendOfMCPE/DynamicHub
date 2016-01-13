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

namespace DynamicHub\Utils;

use DynamicHub\Gamer\Gamer;

abstract class GamerTranslatable implements Translatable{
	private $gamer;

	public function __construct(Gamer $gamer){
		$this->gamer = $gamer;
	}

	public function getGamer() : Gamer{
		return $this->gamer;
	}

	public function __toString() : string{
		return $this->get();
	}
}
