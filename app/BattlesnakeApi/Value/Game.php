<?php

namespace App\BattlesnakeApi\Value;

readonly class Game
{
    public function __construct(
        public string $id,
        public Ruleset $ruleset,
        public string $map,
        public int $timeout,
        public string $source
    ) {
    }
}
