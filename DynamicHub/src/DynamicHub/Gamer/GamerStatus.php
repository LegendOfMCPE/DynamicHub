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

use DynamicHub\Module\Game;
use DynamicHub\Utils\GamerTranslatable;

class GamerStatus extends GamerTranslatable{
	const SCREEN_WIDTH = 40;

	/** @type StatusEntry[][] */
	private $entries = [];
	private $ordered = [];

	public function __construct(Gamer $gamer){
		parent::__construct($gamer);
	}

	public function addEntry(StatusEntry $entry){
		$this->entries[$gameName = $entry->getOwner()->getName()->get()][$id = $entry->getIdentifier()] = $entry;
		$this->ordered[$gameName . ":" . $id] = $entry->getPriority();
		asort($this->ordered);
	}

	public function removeEntry(Game $game, string $identifier){
		unset($this->entries[$name = $game->getName()->get()][$identifier]);
		unset($this->ordered[$name . ":" . $identifier]);
	}

	public function get(string $lang = "en") : string{
		$output = "";
		$outputLength = 0;
		foreach($this->ordered as $name => $p){
			list($name, $id) = explode(":", $name, 2);
			$tag = $this->entries[$name][$id]->get($lang);
			$length = strlen($tag);
			if($outputLength + $length > self::SCREEN_WIDTH){ // TODO calculate character width
				$outputLength = 0;
				$output .= "\n";
			}
			$outputLength += $length;
			$output .= $tag;
		}
		return $output;
	}
}
