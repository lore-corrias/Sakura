<?php

namespace Sakura\Interfaces;


interface SettingsInterface {
    public function __construct(array $settings);
    public function resetSettings(array $settings): void;
    public function getSettings(): array;
    public function changeSettings(array $change): void;
}