<?php

namespace Sakura\Interfaces;


interface LoggerInterface {
	public static function log(string $message, int $type, string $background = '', string $string_color = ''): ?string;
}