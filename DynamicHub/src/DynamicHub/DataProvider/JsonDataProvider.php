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

namespace DynamicHub\DataProvider;

use DynamicHub\DynamicHub;
use DynamicHub\Gamer\GamerData;
use pocketmine\utils\Binary;

class JsonDataProvider implements DataProvider{
	private $hub;
	private $nextId;
	private $dir, $playerDir, $nextIdFile;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
		$this->dir = $hub->getDataFolder() . "data/json/";
		$this->playerDir = $this->dir . "players/";
		$this->nextIdFile = $this->dir . "nextId.txt";
		$this->nextId = (is_file($this->dir) ? Binary::readLong(file_get_contents($this->dir . $this->nextIdFile)) : 0);
	}

	public function fetchData(string $name, DataFetchedCallback $callback){
		$dir = $this->hub->getDataFolder() . "players/";
		$name = strtolower($name);
		$file = $dir . $name . ".json";
		if(!is_file($file)){
			$data = GamerData::defaultInstance($this->hub, $name);
		}else{
			$input = json_decode(file_get_contents($file), true);
			$data = new GamerData($name);
			foreach($input as $k => $v){
				$data->{$k} = $v;
			}
		}
		$callback->onDataFetched($data);
	}

	public function saveData(GamerData $data){
		$dir = $this->hub->getDataFolder() . "players/";
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		$file = $dir . $data->username . ".json";
		file_put_contents($file, json_encode($data,
			JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_SLASHES));
	}

	public function fetchNextId(NextIdFetchedCallback $callback){
		return $this->nextId++;
	}

	public function finalize(){
		file_put_contents($this->nextIdFile, Binary::writeLong($this->nextId));
	}
}
