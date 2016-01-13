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

use DynamicHub\Entry\Goals\Goal;

if(version_compare(PHP_VERSION, "7.0.0", "<")){
	echo "Fatal: This entry script requires PHP >=7.0.0!\n";
	exit;
}

if(!defined("STDIN")){
	define("STDIN", fopen("php://stdin", "r"));
}

spl_autoload_register(function (string $class){
	$holder = Phar::running() . "/entry/";
	$file = $holder . str_replace("\\", "/", $class) . ".php";
	if(is_file($file)){
		require_once $file;
	}else{
		throw new RuntimeException("Class $class not found!");
	}
});

Goal::init();

echo "===DynamicHub by LegendsOfMCPE===\n";
echo "Available actions: ", implode(", ", array_keys(Goal::$goals)), "\n";
echo "What would you like to do?\n";

$line = fgets(STDIN);
$goal = Goal::getGoal(trim($line));
if($goal instanceof Goal){
	$goal->execute();
}

exit(0);
