<?php

namespace App\Snake;

use App\BattlesnakeApi\Enum\MoveDirection;
use App\BattlesnakeApi\Value\Coordinate;

class PossibleMove
{
    public function __construct(
        public MoveDirection $direction,
        public Coordinate $position,
        public int $foodDistance = 0,
    ) {
    }
}
