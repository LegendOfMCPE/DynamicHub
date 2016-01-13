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

use DynamicHub\Module\Module;
use DynamicHub\Utils\GamerTranslatable;

abstract class StatusEntry extends GamerTranslatable{
	const PRIORITY_TIER_TIMER = 1000;
	const PRIORITY_TIER_LOCATION = 2000;
	const PRIORITY_TIER_STATE = 3000;

	public abstract function getPriority() : int;

	public abstract function getIdentifier() : string;

	public abstract function getOwner() : Module;
}
