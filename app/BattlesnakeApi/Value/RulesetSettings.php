<?php

namespace App\BattlesnakeApi\Value;

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
