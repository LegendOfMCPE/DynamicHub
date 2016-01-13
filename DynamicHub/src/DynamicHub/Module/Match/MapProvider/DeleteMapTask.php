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

use DynamicHub\Utils\FileUtils;
use pocketmine\scheduler\AsyncTask;

class DeleteMapTask extends AsyncTask{
	private $dir;

	public function __construct(string $dir){
		$this->dir = $dir;
	}

	public function onRun(){
		FileUtils::removeDirectory($this->dir);
	}
}
