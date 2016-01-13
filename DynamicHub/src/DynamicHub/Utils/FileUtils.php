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

namespace DynamicHub\Utils;

class FileUtils{
	public static function copyDirectory(string $from, string $to){
		$from = rtrim(str_replace("\\", "/", realpath($from)), "/") . "/";
		$to = rtrim(str_replace("\\", "/", realpath($to)), "/") . "/";
		if(!is_dir($from)){
			throw new \InvalidArgumentException("Not a directory");
		}
		if(is_dir($to)){
			throw new \InvalidArgumentException("Target is a directory");
		}
		$dir = dir($from);
		mkdir($to);
		while(($file = $dir->read()) !== false){
			if(is_file($from . $file)){
				copy($from . $file, $to . $file);
			}elseif(is_dir($from . $file) and $file !== "." and $file !== ".."){
				self::copyDirectory($from . $file, $to . $file);
			}
		}
		$dir->close();
	}

	public static function removeDirectory(string $dir){
		$dir = rtrim(str_replace("\\", "/", realpath($dir)), "/") . "/";
		$directory = dir($dir);
		while(($file = $directory->read()) !== false){
			if(is_file($dir . $file)){
				unlink($dir . $file);
			}elseif(is_dir($dir . $file) and $file !== "." and $file !== ".."){
				self::removeDirectory($dir . $file);
			}
		}
		$directory->close();
		rmdir($dir);
	}
}
