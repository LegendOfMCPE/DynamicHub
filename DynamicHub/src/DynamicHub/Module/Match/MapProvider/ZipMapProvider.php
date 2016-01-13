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

use ZipArchive;

class ZipMapProvider extends ThreadedMapProvider{
	private $zipPath;

	public function __construct(string $zipPath){
		if(!is_file($zipPath)){
			throw new \InvalidArgumentException("Invalid zip file");
		}
		$this->zipPath = $zipPath;
	}

	public function extractTo(string $dir) : bool{
		$zip = new ZipArchive;
		$open = $zip->open($this->zipPath);
		if($open !== true){
			switch($open){
				case ZipArchive::ER_EXISTS:
					throw new \RuntimeException("Could not open zip file: File already exists");
				case ZipArchive::ER_INCONS:
					throw new \RuntimeException("Could not open zip file: Zip archive inconsistent");
				case ZipArchive::ER_INVAL:
					throw new \RuntimeException("Could not open zip file: Invalid argument");
				case ZipArchive::ER_MEMORY:
					throw new \RuntimeException("Could not open zip file: Malloc failure");
				case ZipArchive::ER_NOENT:
					throw new \RuntimeException("Could not open zip file: No such file");
				case ZipArchive::ER_NOZIP:
					throw new \RuntimeException("Could not open zip file: Not a zip archive");
				case ZipArchive::ER_OPEN:
					throw new \RuntimeException("Could not open zip file: Can't open file");
				case ZipArchive::ER_READ:
					throw new \RuntimeException("Could not open zip file: Read error");
				case ZipArchive::ER_SEEK:
					throw new \RuntimeException("Could not open zip file: Seek error");
			}
			throw new \RuntimeException("Could not open zip file");
		}
		return $zip->extractTo($dir);
	}
}
