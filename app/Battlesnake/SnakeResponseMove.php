<?php

namespace App\Battlesnake;

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
