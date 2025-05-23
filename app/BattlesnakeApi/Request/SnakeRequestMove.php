<?php

namespace App\BattlesnakeApi\Request;

use App\BattlesnakeApi\Value\Game;
use App\BattlesnakeApi\Value\Board;
use App\BattlesnakeApi\Value\Battlesnake;

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
