<?php

use App\Battlesnake\Enum\MoveDirection;
use App\Battlesnake\Request\SnakeRequestEnd;
use App\Battlesnake\Request\SnakeRequestMove;
use App\Battlesnake\Request\SnakeRequestStart;
use App\Battlesnake\Response\SnakeResponseDetails;
use App\Battlesnake\Response\SnakeResponseMove;
use App\Battlesnake\Value\Coordinate;
use App\Battlesnake\Value\Battlesnake;
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
        [
            'direction' => MoveDirection::UP,
            'x' => $head->x,
            'y' => $head->y + 1,
        ],
        [
            'direction' => MoveDirection::DOWN,
            'x' => $head->x,
            'y' => $head->y - 1,
        ],
        [
            'direction' => MoveDirection::LEFT,
            'x' => $head->x - 1,
            'y' => $head->y,
        ],
        [
            'direction' => MoveDirection::RIGHT,
            'x' => $head->x + 1,
            'y' => $head->y,
        ]
    ];

    foreach ($possibleMoves as $idx => $move) {
        if ($move['x'] < 0 || $move['x'] >= $board->width || $move['y'] < 0 || $move['y'] >= $board->height) {
            unset($possibleMoves[$idx]);
        }
    }

    /** @var Coordinate $part */
    foreach ($throwableSnake->body as $part) {
        foreach ($possibleMoves as $idx => $move) {
            if ($part->x === $move['x'] && $part->y === $move['y']) {
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
            foreach ($possibleMoves as $idx => $move) {
                if ($part->x === $move['x'] && $part->y === $move['y']) {
                    unset($possibleMoves[$idx]);
                }
            }
        }
    }

    foreach ($possibleMoves as $idx => $move) {
        $distances = [];
        /** @var Coordinate $f */
        foreach ($food as $f) {
            $distances[] = abs($f->x - $move['x']) + abs($f->y - $move['y']);
        }
        $possibleMoves[$idx]['distance'] = min($distances);
    }

    if (empty($possibleMoves)) {
        $nextMove = MoveDirection::UP;
    } else {
        usort($possibleMoves, static function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        $nextMove = $possibleMoves[0]['direction'];
    }

    return response(
        $serde->serialize(new SnakeResponseMove(
            move: $nextMove,
        ), 'json'),
        200,
        ['Content-Type' => 'application/json']
    );
});

Route::post('/end', function (Request $request, SerdeCommon $serde) {
    $serde->deserialize($request->getContent(), 'json', SnakeRequestEnd::class);
    return response()->noContent();
});
