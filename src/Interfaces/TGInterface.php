<?php

namespace Sakura\Interfaces;


interface TGInterface {
	public function __get($variable);

	public function __call($name, $arguments);
}