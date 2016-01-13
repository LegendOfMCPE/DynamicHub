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
use DynamicHub\Utils\Translatable;
use pocketmine\plugin\Plugin;

abstract class Module{
	public abstract function getName() : Translatable;

	public final function join(Gamer $gamer){
		$items = $gamer->getData()->getItems($this->getName()->get());
		$inv = $gamer->getPlayer()->getInventory();
		$inv->clearAll(); // rude
		foreach($items as $slot => $item){
			$inv->setItem($slot, $item);
		}
		$this->onJoin($gamer);
	}

	public abstract function onJoin(Gamer $gamer);

	public final function quit(Gamer $gamer){
		$this->onQuit($gamer);
		$inv = $gamer->getPlayer()->getInventory();
		$gamer->getData()->setItems($inv->getContents());
		$inv->clearAll();
	}

	public abstract function onQuit(Gamer $gamer);

	public abstract function getHub() : DynamicHub;

	public abstract function getOwner() : Plugin;

	public function __toString(){
		return $this->getName()->get();
	}
}
