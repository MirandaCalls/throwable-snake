<?php

use App\Battlesnake\Battlesnake;
use App\Battlesnake\Coordinate;
use App\Battlesnake\MoveDirection;
use App\Battlesnake\SnakeRequestEnd;
use App\Battlesnake\SnakeRequestMove;
use App\Battlesnake\SnakeRequestStart;
use App\Battlesnake\SnakeResponseDetails;
use App\Battlesnake\SnakeResponseMove;
use Crell\Serde\SerdeCommon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $serde = new SerdeCommon();
    return response(
        $serde->serialize(new SnakeResponseDetails(
            apiversion: '1',
            author: 'MirandaCalls',
            color: '#be25a8',
            head: 'default',
            tail: 'default',
            version: '0.0.2',
        ), 'json'),
        200,
        ['Content-Type' => 'application/json']
    );
});

Route::post('/start', function (Request $request) {
    $serde = new SerdeCommon();
    $serde->deserialize($request->getContent(), 'json', SnakeRequestStart::class);
    // TODO
    return response()->noContent();
});

Route::post('/move', function (Request $request) {
    $serde = new SerdeCommon();
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

Route::post('/end', function (Request $request) {
    $serde = new SerdeCommon();
    $serde->deserialize($request->getContent(), 'json', SnakeRequestEnd::class);
    // TODO
    return response()->noContent();
});
