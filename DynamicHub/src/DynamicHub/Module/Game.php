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

namespace DynamicHub\Module;

use DynamicHub\DynamicHub;
use DynamicHub\Utils\Translatable;
use pocketmine\plugin\Plugin;

abstract class Game extends Module{
	private $owner;
	/** @type DynamicHub */
	private $hub = null;
	private $name;

	protected function __construct(Plugin $owner, Translatable $name){
		$this->owner = $owner;
		$this->name = $name;
	}

	public function onLoaded(DynamicHub $hub){
		$this->hub = $hub;
	}

	public function halfSecondTick(){
	}

	public final function getOwner() : Plugin{
		return $this->owner;
	}

	public final function getHub() : DynamicHub{
		return $this->hub;
	}

	public function getName() : Translatable{
		return $this->name;
	}
}
