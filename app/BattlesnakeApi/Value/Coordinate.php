<?php

namespace App\BattlesnakeApi\Value;

readonly class Coordinate
{
    public function __construct(
        public int $x,
        public int $y
    ) {
    }

    public function distanceFrom(Coordinate $position): int
    {
        return abs($this->x - $position->x) + abs($this->y - $position->y);
    }
}
