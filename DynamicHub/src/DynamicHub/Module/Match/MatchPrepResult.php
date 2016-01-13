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

namespace DynamicHub\Module\Match;

use DynamicHub\Module\Match\MapProvider\ThreadedMapProvider;

class MatchPrepResult{
	/** @type ThreadedMapProvider */
	private $mapProvider;

	public function getMapProvider() : ThreadedMapProvider{
		return $this->mapProvider;
	}

	/**
	 * @param ThreadedMapProvider $mapProvider
	 *
	 * @return MatchPrepResult
	 */
	public function setMapProvider(ThreadedMapProvider $mapProvider) : MatchPrepResult{
		$this->mapProvider = $mapProvider;
		return $this;
	}
}
