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

use DynamicHub\DynamicHub;
use pocketmine\level\Level;

abstract class JoinMethod{
	public $type;
	public $world = "*";
	public $target;

	public static function get(DynamicHub $hub, $array){
		$type = strtolower($array["type"]);
		if($type === "portal"){
			$class = PortalJoinMethod::class;
		}elseif($type === "sign"){
			$class = SignJoinMethod::class;
		}elseif($type === "key"){
			$class = KeyJoinMethod::class;
		}elseif($type === "command" or $type === "cmd"){
			new JoinGameCommand($hub, $array["name"], $array["target"],
				$array["aliases"] ?? []);
			return null;
		}else{
			return null;
		}
		$method = new $class($array);
		return $method;
	}

	protected function __construct($data){
		foreach($data as $name => $datum){
			$this->{$name} = $datum;
		}
	}

	public function isLevelCorrect(Level $level){
		if($this->world === "*"){
			return true;
		}
		return strtolower($level->getName()) === $this->world;
	}
}
