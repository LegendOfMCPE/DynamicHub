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

namespace DynamicHub\DataProvider;

use DynamicHub\Gamer\GamerData;

interface DataProvider{
	public function fetchData(string $name, DataFetchedCallback $callback);

	public function saveData(GamerData $data);

	public function fetchNextId(NextIdFetchedCallback $callback);

	public function finalize();
}
