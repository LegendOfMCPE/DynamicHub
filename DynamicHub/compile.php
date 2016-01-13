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

chdir(__DIR__);

$i = 0;
function addDir(Phar $phar, $from, $localDir){
	global $i;
	$from = rtrim(realpath($from), "/") . "/";
	$localDir = rtrim($localDir, "/") . "/";
	if(!is_dir($from)){
		echo "WARNING: $from is not a directory!";
		return;
	}
	/** @type SplFileInfo $file */
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from)) as $file){
		if(!$file->isFile()){
			continue;
		}
		$incl = substr($file, strlen($from));
		$target = $localDir . $incl;
		$phar->addFile($file, $target);
		$i++;
		echo "\r[$i] Added file $target";
	}
	echo "\n";
}

function walkPerms(array $stack, array &$perms){
	$prefix = implode(".", $stack) . ".";
	foreach(array_keys($perms) as $key){
		$perms[$prefix . $key] = $perms[$key];
		unset($perms[$key]);
		$stack2 = $stack;
		$stack2[] = $key;
		if(isset($perms[$prefix . $key]["children"])){
			walkPerms($stack2, $perms[$prefix . $key]["children"]);
		}
	}
}

$info = json_decode(file_get_contents("compile/info.json"));
$NAME = $info->name;

$CLASS = "Dev";
$opts = getopt("", ["rc", "beta"]);
if(isset($opts["beta"])){
	$CLASS = "Beta";
}elseif(isset($opts["rc"])){
	$CLASS = "RC";
}

$VERSION = $info->version->major . "." . $info->version->minor . "-" . $CLASS . "#" . ($info->nextBuild++);
file_put_contents("compile/info.json", json_encode($info, JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$permissions = [];
if(is_file("permissions.json")){
	$perms = json_decode(file_get_contents("permissions.json"), true);
	$stack = [];
	walkPerms($stack, $perms);
	$permissions = [];
	foreach($perms as $k => $perm){
		$permissions[substr($k, 1)] = $perm;
	}
}

$file = "compile/" . $NAME . "_" . $CLASS . ".phar";
if(is_file($file)){
	unlink($file);
}
$phar = new Phar($file);
$phar->setStub('<?php require_once "phar://" . __FILE__ . "/entry/entry.php"; __HALT_COMPILER();');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

$phar->addFromString("plugin.yml", yaml_emit([
	"name" => $NAME,
	"author" => $info->author,
	"authors" => $info->authors ?? [],
	"main" => $info->main,
	"api" => $info->api,
	"depend" => $info->depend ?? [],
	"softdepend" => $info->softdepend ?? [],
	"loadbefore" => $info->loadbefore ?? [],
	"description" => $info->description ?? "",
	"website" => $info->website ?? "",
	"prefix" => $info->prefix ?? $NAME,
	"load" => $info->load ?? "POSTWORLD",
	"version" => $VERSION,
	"permissions" => $permissions
]));
addDir($phar, "src", "src");
addDir($phar, "entry", "entry");
addDir($phar, "resources", "resources");
$phar->stopBuffering();
