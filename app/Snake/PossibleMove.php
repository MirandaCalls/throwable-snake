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
        public bool $isKillingMove = false,
        public int $huntDistance = 0,
        public int $spaceAvailable = 0,
    ) {
    }

    public function isOutOfBounds(Board $board): bool
    {
        return $this->position->x < 0 ||
            $this->position->x >= $board->width ||
            $this->position->y < 0 ||
            $this->position->y >= $board->height;
    }

    public function floodFill(Board $board, array $snakes): int
    {
        $blocked = [];
        foreach ($snakes as $snake) {
            foreach (array_slice($snake->body, 0, -1) as $part) {
                $blocked[$part->x . ',' . $part->y] = true;
            }
        }

        $visited = [];
        $queue = [$this->position];
        $count = 0;

        while (!empty($queue)) {
            $current = array_shift($queue);
            $key = $current->x . ',' . $current->y;

            if (isset($visited[$key])) {
                continue;
            }
            $visited[$key] = true;
            $count++;

            foreach ([
                new Coordinate($current->x, $current->y + 1),
                new Coordinate($current->x, $current->y - 1),
                new Coordinate($current->x - 1, $current->y),
                new Coordinate($current->x + 1, $current->y),
            ] as $neighbor) {
                $neighborKey = $neighbor->x . ',' . $neighbor->y;
                if (
                    $neighbor->x < 0 || $neighbor->x >= $board->width ||
                    $neighbor->y < 0 || $neighbor->y >= $board->height ||
                    isset($visited[$neighborKey]) ||
                    isset($blocked[$neighborKey])
                ) {
                    continue;
                }
                $queue[] = $neighbor;
            }
        }

        return $count;
    }

    public function isAdjacentToSnakeHead(Battlesnake $snake): bool
    {
        $head = $snake->head;
        return ($this->position->x === $head->x && abs($this->position->y - $head->y) === 1)
            || ($this->position->y === $head->y && abs($this->position->x - $head->x) === 1);
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
