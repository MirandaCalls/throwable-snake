<?php

namespace App\Battlesnake;

readonly class RulesetSettingsSquad
{
    public function __construct(
        public bool $allowBodyCollisions,
        public bool $sharedElimination,
        public bool $sharedHealth,
        public bool $sharedLength,
    ) {
    }
}
