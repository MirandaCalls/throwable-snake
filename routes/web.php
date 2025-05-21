<?php

use App\Battlesnake\SnakeDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(new SnakeDetails(
        apiversion: '1',
        author: 'MirandaCalls',
        color: '#be25a8',
        head: 'default',
        tail: 'default',
        version: '0.0.2',
    ));
});

Route::post('/move', function (Request $request) {
    $gameData = $request->json()->all();
    $board = $gameData['board'];
    $food = $board['food'];
    $snakes = $gameData['board']['snakes'];
    $throwableSnake = $gameData['you'];
    $head = $throwableSnake['head'];

    $possibleMoves = [
        [
            'name' => 'up',
            'x' => $head['x'],
            'y' => $head['y'] + 1,
        ],
        [
            'name' => 'down',
            'x' => $head['x'],
            'y' => $head['y'] - 1,
        ],
        [
            'name' => 'left',
            'x' => $head['x'] - 1,
            'y' => $head['y'],
        ],
        [
            'name' => 'right',
            'x' => $head['x'] + 1,
            'y' => $head['y'],
        ]
    ];

    foreach ($possibleMoves as $idx => $move) {
        if ($move['x'] < 0 || $move['x'] >= $board['width'] || $move['y'] < 0 || $move['y'] >= $board['height']) {
            unset($possibleMoves[$idx]);
        }
    }

    foreach ($throwableSnake['body'] as $part) {
        foreach ($possibleMoves as $idx => $move) {
            if ($part['x'] === $move['x'] && $part['y'] === $move['y']) {
                unset($possibleMoves[$idx]);
            }
        }
    }

    foreach ($snakes as $snake) {
        if ($snake['id'] === $throwableSnake['id']) {
            continue;
        }

        foreach ($snake['body'] as $part) {
            foreach ($possibleMoves as $idx => $move) {
                if ($part['x'] === $move['x'] && $part['y'] === $move['y']) {
                    unset($possibleMoves[$idx]);
                }
            }
        }
    }

    foreach ($possibleMoves as $idx => $move) {
        $distances = [];
        foreach ($food as $f) {
            $distances[] = abs($f['x'] - $move['x']) + abs($f['y'] - $move['y']);
        }
        $possibleMoves[$idx]['distance'] = min($distances);
    }

    if (empty($possibleMoves)) {
        $nextMove = 'up';
    } else {
        usort($possibleMoves, static function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        $nextMove = $possibleMoves[0]['name'];
    }

    return response()->json([
        'move' => $nextMove,
    ]);
});
