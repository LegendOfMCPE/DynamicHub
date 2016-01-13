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

namespace DynamicHub\Config\JoinMethod;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class PortalJoinMethod extends JoinMethod{
	/** @type int[] */
	public $start, $end;
	public $bb;

	public function __construct($data){
		parent::__construct($data);
		$this->bb = new AxisAlignedBB(
			min($this->start[0], $this->end[0]),
			min($this->start[1], $this->end[1]),
			min($this->start[2], $this->end[2]),
			max($this->start[0], $this->end[0]) + 1,
			max($this->start[1], $this->end[1]),
			max($this->start[2], $this->end[2]) + 1
		);
	}

	public function isInside(Vector3 $pos){
		return $this->bb->isVectorInside($pos);
	}
}
