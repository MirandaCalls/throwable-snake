<?php

namespace App\BattlesnakeApi\Value;

readonly class Coordinate
{
    public function __construct(
        public int $x,
        public int $y
    ) {
    }
}
