<?php

namespace App\Battlesnake;

readonly class RulesetSettings
{
    public function __construct(
        public int $foodSpawnChance,
        public int $minimumFood,
        public int $hazardDamagePerTurn,
        public RulesetSettingsRoyale $royale,
        public RulesetSettingsSquad $squad,
    ) {
    }
}
