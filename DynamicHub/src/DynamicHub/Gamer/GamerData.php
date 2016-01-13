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

namespace DynamicHub\Gamer;

use DynamicHub\DynamicHub;
use DynamicHub\Module\HubModule;
use pocketmine\item\Item;

class GamerData{
	/** @type string */
	public $username;
	/** @type string */
	public $lastModule;
	/** @type array[][] */
	public $inventories = [];

	public static function defaultInstance(DynamicHub $hub, string $username) : GamerData{
		$data = new GamerData(strtolower($username));
		$data->lastModule = HubModule::NAME;
		$data->inventories = [
			strtolower(HubModule::NAME) => $hub->getConfig()->getNested("hub.defaultItems"),
		];
		return $data;
	}

	public function __construct(string $username){
		$this->username = $username;
	}

	/**
	 * @param string $module
	 *
	 * @return Item[]
	 */
	public function getItems(string $module = HubModule::NAME) : array{
		$output = [];
		foreach($this->inventories[strtolower($module)] ?? [] as $slot => $array){
			$output[$slot] = Item::get($array["id"], $array["damage"] ?? 0, $array["count"] ?? 1);
		}
		return $output;
	}

	/**
	 * @param Item[] $items
	 * @param string $module
	 */
	public function setItems(array $items, string $module = HubModule::NAME){
		foreach($items as $slot => $item){
			$object = ["id" => $item->getId()];
			if($item->getDamage()){
				$object["damage"] = $item->getDamage();
			}
			if($item->getCount() > 1){
				$object["count"] = $item->getCount();
			}
			$this->inventories[strtolower($module)][$slot] = $item;
		}
	}
}
