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

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class CallbackPluginTask extends PluginTask{
	/**
	 * @type Plugin
	 */
	private $plugin;
	/**
	 * @type callable
	 */
	private $callback;

	public function __construct(Plugin $plugin, callable $callback){
		self::validateCallback($callback);
		parent::__construct($this->plugin = $plugin);
		$this->callback = $callback;
	}

	public function onRun($currentTick){
		$callback = $this->callback;
		$callback();
	}

	/**
	 * Ensure that this callback takes no arguments
	 *
	 * @param callable $cb
	 */
	public static function validateCallback(callable $cb){
		if(is_string($cb) and strpos($cb, "::") !== false){
			$cb = explode("::", $cb);
		}
		if(is_string($cb) or $cb instanceof \Closure){
			$function = new \ReflectionFunction($cb);
			if($function->getNumberOfRequiredParameters() > 0){
				throw new \InvalidArgumentException("Callable requires parameters");
			}
		}elseif(is_array($cb)){
			list($ctx, $m) = $cb;
			$method = new \ReflectionMethod($ctx, $m);
			if($method->getNumberOfRequiredParameters() > 0){
				throw new \InvalidArgumentException("Callable requires parameters");
			}
		}
	}
}
