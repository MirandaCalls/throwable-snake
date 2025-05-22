<?php

namespace App\Battlesnake;

readonly class SnakeRequestEnd
{
    public function __construct(
        public Game $game,
        public int $turn,
        public Board $board,
        public Battlesnake $you,
    ) {
    }
}
