<?php

namespace Sakura\Interfaces;

use Sakura\TGBot;


interface InterfaceAPIRequest {
	public function __construct(string $method, array $params, ?TGBot $tg = NULL, string $api = 'https://api.telegram.org/bot');

	public function getResponse();

	//public static function getCurrentInstance();
}