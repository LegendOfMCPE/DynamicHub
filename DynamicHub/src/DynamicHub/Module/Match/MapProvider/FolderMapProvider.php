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

use DynamicHub\Utils\FileUtils;

class FolderMapProvider extends ThreadedMapProvider{
	private $dir;

	public function __construct(string $dir){
		if(!is_dir($dir)){
			throw new \InvalidArgumentException($dir . " is not a directory!");
		}
		$this->dir = $dir;
	}

	public function extractTo(string $dir) : bool{
		FileUtils::copyDirectory($this->dir, $dir);
	}
}
