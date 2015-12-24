<?php

/*
 * CaptureTheFlag
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

namespace DynamicHub\CaptureTheFlag;

use DynamicHub\Module\Match\Match;
use DynamicHub\Module\Match\MatchBaseConfig;

class CTFMatch extends Match{
	public function __construct(CTFGame $game, $id){
		parent::__construct($game, $id);
	}

	public function getBaseWorldZip() : \ZipArchive{
		// TODO: Implement getBaseWorldZip() method.
	}

	public function getMatchConfig() : MatchBaseConfig{
		// TODO: Implement getMatchConfig() method.
	}
}
