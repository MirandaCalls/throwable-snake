<?php

namespace App\BattlesnakeApi\Value;

readonly class RulesetSettingsRoyale
{
    public function __construct(
        public int $shrinkEveryNTurns,
    ) {
    }
}
