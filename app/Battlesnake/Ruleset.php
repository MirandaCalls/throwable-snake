<?php

namespace App\Battlesnake;

readonly class Ruleset
{
    public function __construct(
        public string $name,
        public string $version,
        public RulesetSettings $settings,
    ) {
    }
}
