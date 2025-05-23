<?php

namespace App\BattlesnakeApi\Value;

use Crell\Serde\Attributes as Serde;

readonly class Battlesnake
{
    public function __construct(
        public string $id,
        public string $name,
        public int $health,
        /** @var Coordinate[] $body */
        #[Serde\SequenceField(arrayType: Coordinate::class)]
        public array $body,
        public string $latency,
        public Coordinate $head,
        public int $length,
        public string $shout,
        public string $squad,
        public BattlesnakeCustomizations $customizations,
    ) {
    }
}
