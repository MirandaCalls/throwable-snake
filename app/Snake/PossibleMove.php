<?php

namespace App\Snake;

use App\BattlesnakeApi\Enum\MoveDirection;
use App\BattlesnakeApi\Value\Battlesnake;
use App\BattlesnakeApi\Value\Board;
use App\BattlesnakeApi\Value\Coordinate;

class PossibleMove
{
    private ?int       $foodDistanceCache  = null;
    private ?bool      $isKillingMoveCache = null;
    private int|null|false $huntDistanceCache  = false;

    public function __construct(
        private readonly Board        $board,
        private readonly Battlesnake  $owningSnake,
        public readonly MoveDirection $direction,
        public readonly Coordinate    $position,
        private int                   $spaceAvailable = 0,
        private int                   $floodFillSpace = 0,
    ) {
    }

    public function isOutOfBounds(): bool
    {
        return $this->position->x < 0 ||
            $this->position->x >= $this->board->width ||
            $this->position->y < 0 ||
            $this->position->y >= $this->board->height;
    }

    public function voronoiTerritory(): int
    {
        if ($this->spaceAvailable !== 0) {
            return $this->spaceAvailable;
        }

        $blocked = [];
        foreach ($this->board->snakes as $snake) {
            foreach (array_slice($snake->body, 0, -1) as $part) {
                $blocked[$part->x . ',' . $part->y] = true;
            }
        }

        $visited = [];
        $queue = [];

        $ourKey = $this->position->x . ',' . $this->position->y;
        $visited[$ourKey] = $this->owningSnake->id;
        $queue[] = [$this->position->x, $this->position->y, $this->owningSnake->id];

        foreach ($this->board->snakes as $snake) {
            if ($snake->id === $this->owningSnake->id) {
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
                if ($nx < 0 || $nx >= $this->board->width || $ny < 0 || $ny >= $this->board->height) {
                    continue;
                }
                $neighborKey = $nx . ',' . $ny;
                if (isset($visited[$neighborKey]) || isset($blocked[$neighborKey])) {
                    continue;
                }
                $visited[$neighborKey] = $snakeId;
                if ($snakeId === $this->owningSnake->id) {
                    $ourCount++;
                }
                $queue[] = [$nx, $ny, $snakeId];
            }
        }

        $this->spaceAvailable = $ourCount;

        return $this->spaceAvailable;
    }

    public function floodFill(): int
    {
        if ($this->floodFillSpace !== 0) {
            return $this->floodFillSpace;
        }
        $blocked = [];
        foreach ($this->board->snakes as $snake) {
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
                if ($nx < 0 || $nx >= $this->board->width || $ny < 0 || $ny >= $this->board->height) {
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

        $this->floodFillSpace = $count;

        return $this->floodFillSpace;
    }

    public function isAdjacentToSnakeHead(Battlesnake $snake): bool
    {
        $head = $snake->head;
        return ($this->position->x === $head->x && abs($this->position->y - $head->y) === 1)
            || ($this->position->y === $head->y && abs($this->position->x - $head->x) === 1);
    }

    public function isDangerous(): bool
    {
        foreach ($this->board->snakes as $snake) {
            if ($snake->id === $this->owningSnake->id) {
                continue;
            }
            if ($this->isAdjacentToSnakeHead($snake) && $this->owningSnake->length <= $snake->length) {
                return true;
            }
        }
        return false;
    }

    public function collidesWithAnySnake(): bool
    {
        foreach ($this->board->snakes as $snake) {
            foreach ($snake->body as $part) {
                if ($part->x === $this->position->x && $part->y === $this->position->y) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isKillingMove(): bool
    {
        if ($this->isKillingMoveCache !== null) {
            return $this->isKillingMoveCache;
        }
        foreach ($this->board->snakes as $snake) {
            if ($snake->id === $this->owningSnake->id) {
                continue;
            }
            if ($this->isAdjacentToSnakeHead($snake) && $this->owningSnake->length > $snake->length) {
                return $this->isKillingMoveCache = true;
            }
        }
        return $this->isKillingMoveCache = false;
    }

    public function foodDistance(): int
    {
        if ($this->foodDistanceCache !== null) {
            return $this->foodDistanceCache;
        }
        if (empty($this->board->food)) {
            return $this->foodDistanceCache = 0;
        }
        return $this->foodDistanceCache = min(
            array_map(
                fn(Coordinate $f): int => $this->position->distanceFrom($f),
                $this->board->food
            )
        );
    }

    public function huntDistance(): ?int
    {
        if ($this->huntDistanceCache !== false) {
            return $this->huntDistanceCache;
        }
        $huntTarget = null;
        $minDistance = PHP_INT_MAX;
        foreach ($this->board->snakes as $snake) {
            if ($snake->id === $this->owningSnake->id || $snake->length >= $this->owningSnake->length) {
                continue;
            }
            $d = $this->owningSnake->head->distanceFrom($snake->head);
            if ($d < $minDistance) {
                $minDistance = $d;
                $huntTarget  = $snake;
            }
        }
        if ($huntTarget === null) {
            return $this->huntDistanceCache = null;
        }
        return $this->huntDistanceCache = $this->position->distanceFrom($huntTarget->head);
    }
}
