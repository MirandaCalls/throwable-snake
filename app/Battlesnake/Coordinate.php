<?php

namespace App\Battlesnake;

readonly class Coordinate
{
    public function __construct(
        public int $x,
        public int $y
    ) {
    }
}
