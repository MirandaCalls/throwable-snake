<?php

namespace App\Battlesnake;

readonly class SnakeRequestMove
{
    public function __construct(
        public Game $game,
        public int $turn,
        public Board $board,
        public Battlesnake $you,
    ) {
    }
}
