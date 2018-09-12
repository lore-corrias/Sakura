<?php

namespace sakura\Interfaces;


interface PluginsManagerInterface
{
    public function __construct();
    public function isOfficialPlugin(): bool;
    public function downloadPlugin(): void;
    public function checkPluginsValidity(): void;
}