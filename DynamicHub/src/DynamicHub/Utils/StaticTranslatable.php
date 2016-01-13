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

class StaticTranslatable implements Translatable{
	/** @type string[] */
	private $langs;

	public function __construct(string $en){
		$this->langs = ["en" => $en];
	}

	public function addLang(string $lang, string $value){
		$this->langs[$lang] = $value;
	}

	public function get(string $lang = "en") : string{
		return isset($this->langs[$lang]) ? $this->langs[$lang] : $this->langs["en"];
	}

	public function __toString() : string{
		return $this->get();
	}
}
