<?php

namespace App\BattlesnakeApi\Value;

use App\BattlesnakeApi\Enum\MoveDirection;
use App\Snake\PossibleMove;
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

    /** @return PossibleMove[] */
    public function possibleMoves(Board $board): array
    {
        return [
            new PossibleMove(board: $board, owningSnake: $this, direction: MoveDirection::UP,    position: new Coordinate(x: $this->head->x,     y: $this->head->y + 1)),
            new PossibleMove(board: $board, owningSnake: $this, direction: MoveDirection::DOWN,  position: new Coordinate(x: $this->head->x,     y: $this->head->y - 1)),
            new PossibleMove(board: $board, owningSnake: $this, direction: MoveDirection::LEFT,  position: new Coordinate(x: $this->head->x - 1, y: $this->head->y)),
            new PossibleMove(board: $board, owningSnake: $this, direction: MoveDirection::RIGHT, position: new Coordinate(x: $this->head->x + 1, y: $this->head->y)),
        ];
    }
}
