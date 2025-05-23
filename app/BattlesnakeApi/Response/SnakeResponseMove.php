<?php

namespace App\BattlesnakeApi\Response;

use App\BattlesnakeApi\Enum\MoveDirection;
use Crell\Serde\Attributes as Serde;

#[Serde\ClassSettings(omitNullFields: true)]
class SnakeResponseMove
{
    public function __construct(
        public MoveDirection $move,
        public ?string $shout = null
    ) {
    }
}
