<?php

namespace App\Snake;

use App\BattlesnakeApi\Enum\MoveDirection;
use App\BattlesnakeApi\Value\Battlesnake;
use App\BattlesnakeApi\Value\Board;
use App\BattlesnakeApi\Value\Coordinate;

class PossibleMove
{
    public function __construct(
        public MoveDirection $direction,
        public Coordinate $position,
        public int $foodDistance = 0,
    ) {
    }

    public function isOutOfBounds(Board $board): bool
    {
        return $this->position->x < 0 ||
            $this->position->x >= $board->width ||
            $this->position->y < 0 ||
            $this->position->y >= $board->height;
    }

    public function collidesWithSnake(Battlesnake $snake): bool
    {
        /** @var Coordinate $part */
        foreach ($snake->body as $part) {
            if ($part->x === $this->position->x && $part->y === $this->position->y) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PossibleMove[]
     **/
    public static function possibleMovesFromPosition(Coordinate $position): array
    {
        return [
            new PossibleMove(
                direction: MoveDirection::UP,
                position: new Coordinate(
                    x: $position->x,
                    y: $position->y + 1,
                )
            ),
            new PossibleMove(
                direction: MoveDirection::DOWN,
                position: new Coordinate(
                    x: $position->x,
                    y: $position->y - 1,
                )
            ),
            new PossibleMove(
                direction: MoveDirection::LEFT,
                position: new Coordinate(
                    x: $position->x - 1,
                    y: $position->y,
                )
            ),
            new PossibleMove(
                direction: MoveDirection::RIGHT,
                position: new Coordinate(
                    x: $position->x + 1,
                    y: $position->y,
                )
            ),
        ];
    }
}
