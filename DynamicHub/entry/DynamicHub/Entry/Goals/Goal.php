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

namespace DynamicHub\Entry\Goals;

abstract class Goal{
	const GOAL_NAME = "unnamed";

	/** @type array */
	public static $goals = [self::GOAL_NAME => false];

	public static function init(){
		self::registerGoal(InstallerGoal::class);
	}

	public static function registerGoal($class){
		if(isset(self::$goals[strtolower($class::GOAL_NAME)])){
			throw new \RuntimeException("Goal " . $class::GOAL_NAME . " ($class) is already registered!");
		}
		self::$goals[strtolower($class::GOAL_NAME)] = $class;
	}

	public abstract function execute();

	public static function getGoal($name){
		if(isset(self::$goals[strtolower($name)]) and self::$goals[strtolower($name)] instanceof Goal){
			return self::$goals[strtolower($name)];
		}
		return null;
	}
}
