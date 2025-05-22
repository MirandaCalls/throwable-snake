<?php

namespace App\Battlesnake;

readonly class RulesetSettingsRoyale
{
    public function __construct(
        public int $shrinkEveryNTurns,
    ) {
    }
}
