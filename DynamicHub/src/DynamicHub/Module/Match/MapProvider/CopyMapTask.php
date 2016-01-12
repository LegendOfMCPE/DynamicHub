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

namespace DynamicHub\Module\Match\MapProvider;

use DynamicHub\DynamicHub;
use DynamicHub\Module\Match\Match;
use DynamicHub\Module\Match\MatchBasedGame;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CopyMapTask extends AsyncTask{
	private $provider;
	private $prefix;
	private $gameName;
	private $matchId;

	public function __construct(ThreadedMapProvider $provider, $mapName, Match $match){
		$this->provider = $provider;
		$this->prefix = $match->getGame()->getHub()->getServer()->getDataPath() . "worlds/room-$mapName-";
		$this->gameName = $match->getGame()->getName()->get();
		$this->matchId = $match->getMatchId();
	}

	public function onRun(){
		for($i = 0; true; $i++){
			if(!is_dir($this->prefix . $i)){
				break;
			}
		}
		mkdir($dir = $this->prefix . $i);
		$this->provider->extractTo($dir);
		$this->setResult(substr($dir, 7));
	}

	public function onCompletion(Server $server){
		$hub = DynamicHub::getInstance($server);
		if($hub !== null){
			$game = $hub->getLoadedGame($this->gameName);
			if($game instanceof MatchBasedGame){
				$match = $game->getMatchById($this->matchId);
				if($match !== null){
					$match->onMapLoaded($this->getResult());
				}
			}
		}
	}
}
