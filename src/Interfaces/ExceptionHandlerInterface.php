<?php

namespace Sakura\Interfaces;


interface ExceptionHandlerInterface
{
    public function __construct(?string $log_dir = NULL, ?callable $handler = NULL);

    public function handler(\Throwable $exception);

    public function getUserHandler(): ?callable;
}