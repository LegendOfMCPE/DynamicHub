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

namespace DynamicHub\Config\JoinMethod;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

class KeyJoinMethod extends JoinMethod{
	/** @type Item */
	public $key;
	/** @type Vector3 */
	public $lock;

	public function __construct($data){
		parent::__construct($data);
		$this->key = isset($this->key) ? Item::get($this->key["itemId"], $this->key["damage"]) : null;
		$this->lock = isset($this->lock) ? new Vector3(...$this->lock) : null;
	}
}
