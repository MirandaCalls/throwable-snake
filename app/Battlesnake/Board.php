<?php

namespace App\Battlesnake;

use Crell\Serde\Attributes as Serde;

readonly class Board
{
    public function __construct(
        public int $height,
        public int $width,
        /** @var Coordinate[] $food */
        #[Serde\SequenceField(arrayType: Coordinate::class)]
        public array $food,
        /** @var Coordinate[] $hazards */
        #[Serde\SequenceField(arrayType: Coordinate::class)]
        public array $hazards,
        /** @var Coordinate[] $snakes */
        #[Serde\SequenceField(arrayType: Battlesnake::class)]
        public array $snakes
    ) {
    }
}
