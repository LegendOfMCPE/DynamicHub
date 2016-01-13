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
use DynamicHub\Gamer\Gamer;
use DynamicHub\Utils\StaticTranslatable;
use DynamicHub\Utils\Translatable;
use pocketmine\plugin\Plugin;

class HubModule extends Module{
	const NAME = "Hub";

	private $main;

	public function __construct(DynamicHub $main){
		$this->main = $main;
	}

	public function getName() : Translatable{
		return new StaticTranslatable(self::NAME); // TODO translate
	}

	public function onJoin(Gamer $gamer){
		$gamer->setDefaultVisible(true);
	}

	public function onQuit(Gamer $gamer){
	}

	public function getHub() : DynamicHub{
		return $this->main;
	}

	public function getOwner() : Plugin{
		return $this->main;
	}
}
