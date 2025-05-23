<?php

namespace App\BattlesnakeApi\Value;

readonly class Ruleset
{
    public function __construct(
        public string $name,
        public string $version,
        public RulesetSettings $settings,
    ) {
    }
}
