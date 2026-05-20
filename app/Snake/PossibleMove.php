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
        public int $floodFillSpace = 0,
    ) {
    }

    public function isOutOfBounds(Board $board): bool
    {
        return $this->position->x < 0 ||
            $this->position->x >= $board->width ||
            $this->position->y < 0 ||
            $this->position->y >= $board->height;
    }

    public function voronoiTerritory(Board $board, Battlesnake $you, array $allSnakes): int
    {
        $blocked = [];
        foreach ($allSnakes as $snake) {
            foreach (array_slice($snake->body, 0, -1) as $part) {
                $blocked[$part->x . ',' . $part->y] = true;
            }
        }

        $visited = [];
        $queue = [];

        $ourKey = $this->position->x . ',' . $this->position->y;
        $visited[$ourKey] = $you->id;
        $queue[] = [$this->position->x, $this->position->y, $you->id];

        foreach ($allSnakes as $snake) {
            if ($snake->id === $you->id) {
                continue;
            }
            $headKey = $snake->head->x . ',' . $snake->head->y;
            if (!isset($visited[$headKey])) {
                $visited[$headKey] = $snake->id;
                $queue[] = [$snake->head->x, $snake->head->y, $snake->id];
            }
        }

        $ourCount = 1;

        while (!empty($queue)) {
            [$x, $y, $snakeId] = array_shift($queue);

            foreach ([[$x, $y + 1], [$x, $y - 1], [$x - 1, $y], [$x + 1, $y]] as [$nx, $ny]) {
                if ($nx < 0 || $nx >= $board->width || $ny < 0 || $ny >= $board->height) {
                    continue;
                }
                $neighborKey = $nx . ',' . $ny;
                if (isset($visited[$neighborKey]) || isset($blocked[$neighborKey])) {
                    continue;
                }
                $visited[$neighborKey] = $snakeId;
                if ($snakeId === $you->id) {
                    $ourCount++;
                }
                $queue[] = [$nx, $ny, $snakeId];
            }
        }

        return $ourCount;
    }

    public function floodFill(Board $board, array $allSnakes): int
    {
        $blocked = [];
        foreach ($allSnakes as $snake) {
            foreach (array_slice($snake->body, 0, -1) as $part) {
                $blocked[$part->x . ',' . $part->y] = true;
            }
        }

        $startKey = $this->position->x . ',' . $this->position->y;
        if (isset($blocked[$startKey])) {
            return 0;
        }

        $visited = [$startKey => true];
        $queue = [[$this->position->x, $this->position->y]];
        $count = 0;

        while (!empty($queue)) {
            [$x, $y] = array_shift($queue);
            $count++;

            foreach ([[$x, $y + 1], [$x, $y - 1], [$x - 1, $y], [$x + 1, $y]] as [$nx, $ny]) {
                if ($nx < 0 || $nx >= $board->width || $ny < 0 || $ny >= $board->height) {
                    continue;
                }
                $neighborKey = $nx . ',' . $ny;
                if (isset($visited[$neighborKey]) || isset($blocked[$neighborKey])) {
                    continue;
                }
                $visited[$neighborKey] = true;
                $queue[] = [$nx, $ny];
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
