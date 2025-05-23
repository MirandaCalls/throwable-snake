<?php

namespace App\BattlesnakeApi\Enum;

enum MoveDirection: string
{
    case UP = "up";
    case DOWN = "down";
    case LEFT = "left";
    case RIGHT = "right";
}
