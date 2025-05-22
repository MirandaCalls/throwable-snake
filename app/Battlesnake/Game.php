<?php

namespace App\Battlesnake;

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
