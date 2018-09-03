<?php

namespace Sakura\Interfaces;


use Sakura\TGBot;


interface UpdateHandlerInterface {
	public function getUpdates(int $offset, array $allowed = [], int $limit = 100, int $timeout = 3);

	public function __construct(callable $update_function_handler, ?TGBot $tg = NULL);

    public function loop();
}