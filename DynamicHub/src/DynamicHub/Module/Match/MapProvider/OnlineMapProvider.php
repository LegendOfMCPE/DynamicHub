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

namespace DynamicHub\Module\Match\MapProvider;

use PharData;
use pocketmine\utils\Utils;

class OnlineMapProvider extends ThreadedMapProvider{
	private $url;
	private $format;

	public function __construct(string $url, int $mode = PharData::ZIP){
		$this->url = $url;
		$this->format = $mode;
	}

	public function extractTo(string $dir) : bool{
		$buffer = Utils::getURL($this->url);
		$file = tempnam(sys_get_temp_dir(), "DynHub");
		file_put_contents($file, $buffer);
		$data = new PharData($file, null, null, $this->format);
		$output = $data->extractTo($dir, null, true);
		unlink($file);
		return $output;
	}
}
