<?php

use App\BattlesnakeApi\Enum\MoveDirection;
use App\BattlesnakeApi\Request\SnakeRequestEnd;
use App\BattlesnakeApi\Request\SnakeRequestMove;
use App\BattlesnakeApi\Request\SnakeRequestStart;
use App\BattlesnakeApi\Response\SnakeResponseDetails;
use App\BattlesnakeApi\Response\SnakeResponseMove;
use App\BattlesnakeApi\Value\Coordinate;
use App\BattlesnakeApi\Value\Battlesnake;
use App\Snake\ExceptionGenerator;
use App\Snake\PossibleMove;
use Crell\Serde\SerdeCommon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (SerdeCommon $serde) {
    return response(
        $serde->serialize(new SnakeResponseDetails(
            apiversion: '1',
            author: 'MirandaCalls',
            color: '#be25a8',
            head: 'default',
            tail: 'default',
            version: '0.0.3',
        ), 'json'),
        200,
        ['Content-Type' => 'application/json']
    );
});

Route::post('/start', function (Request $request, SerdeCommon $serde) {
    $serde->deserialize($request->getContent(), 'json', SnakeRequestStart::class);
    return response()->noContent();
});

Route::post('/move', function (Request $request, SerdeCommon $serde) {
    $data = $serde->deserialize($request->getContent(), 'json', SnakeRequestMove::class);

    $board = $data->board;
    $food = $board->food;
    $snakes = $board->snakes;
    $throwableSnake = $data->you;
    $head = $throwableSnake->head;

    $possibleMoves = [
        new PossibleMove(
            direction: MoveDirection::UP,
            position: new Coordinate(
                x: $head->x,
                y: $head->y + 1,
            )
        ),
        new PossibleMove(
            direction: MoveDirection::DOWN,
            position: new Coordinate(
                x: $head->x,
                y: $head->y - 1,
            )
        ),
        new PossibleMove(
            direction: MoveDirection::LEFT,
            position: new Coordinate(
                x: $head->x - 1,
                y: $head->y,
            )
        ),
        new PossibleMove(
            direction: MoveDirection::RIGHT,
            position: new Coordinate(
                x: $head->x + 1,
                y: $head->y,
            )
        ),
    ];

    /** @var PossibleMove $move */
    foreach ($possibleMoves as $idx => $move) {
        if ($move->position->x < 0 ||
            $move->position->x >= $board->width ||
            $move->position->y < 0 ||
            $move->position->y >= $board->height
        ) {
            unset($possibleMoves[$idx]);
        }
    }

    /** @var Coordinate $part */
    foreach ($throwableSnake->body as $part) {
        /** @var PossibleMove $move */
        foreach ($possibleMoves as $idx => $move) {
            if ($part->x === $move->position->x && $part->y === $move->position->y) {
                unset($possibleMoves[$idx]);
            }
        }
    }

    /** @var Battlesnake $snake */
    foreach ($snakes as $snake) {
        if ($snake->id === $throwableSnake->id) {
            continue;
        }

        /** @var Coordinate $part */
        foreach ($snake->body as $part) {
            /** @var PossibleMove $move */
            foreach ($possibleMoves as $idx => $move) {
                if ($part->x === $move->position->x && $part->y === $move->position->y) {
                    unset($possibleMoves[$idx]);
                }
            }
        }
    }

    /** @var PossibleMove $move */
    foreach ($possibleMoves as $idx => $move) {
        $distances = [];
        /** @var Coordinate $f */
        foreach ($food as $f) {
            $distances[] = abs($f->x - $move->position->x) + abs($f->y - $move->position->y);
        }
        $possibleMoves[$idx]->foodDistance = min($distances);
    }

    if (empty($possibleMoves)) {
        $nextMove = MoveDirection::UP;
    } else {
        usort($possibleMoves, static function ($a, $b) {
            return $a->foodDistance <=> $b->foodDistance;
        });
        $nextMove = $possibleMoves[0]->direction;
    }

    $shout = null;
    if (random_int(1, 100) > 90) {
        $exceptionGenerator = new ExceptionGenerator();
        $shout = $exceptionGenerator->randomMessage();
    }

    return response(
        $serde->serialize(new SnakeResponseMove(
            move: $nextMove,
            shout: $shout,
        ), 'json'),
        200,
        ['Content-Type' => 'application/json']
    );
});

Route::post('/end', function (Request $request, SerdeCommon $serde) {
    $serde->deserialize($request->getContent(), 'json', SnakeRequestEnd::class);
    return response()->noContent();
});
