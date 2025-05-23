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
    $config = config('snake');
    return response(
        $serde->serialize(new SnakeResponseDetails(
            apiversion: $config['apiversion'],
            author: $config['author'],
            color: $config['color'],
            head: $config['head'],
            tail: $config['tail'],
            version: $config['version'],
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
    $throwableSnake = $data->you;

    $possibleMoves = array_filter(
        PossibleMove::possibleMovesFromPosition($throwableSnake->head),
        static fn (PossibleMove $move): bool => !$move->isOutOfBounds($board)
    );

    /** @var Battlesnake $snake */
    foreach ($board->snakes as $snake) {
        $possibleMoves = array_filter(
            $possibleMoves,
            static fn (PossibleMove $move): bool => !$move->collidesWithSnake($snake)
        );
    }

    /** @var PossibleMove $move */
    foreach ($possibleMoves as $move) {
        $move->foodDistance = min(
            array_map(
                static fn(Coordinate $f): int => $move->position->distanceFrom($f),
                $board->food
            )
        );
    }

    if (empty($possibleMoves)) {
        $nextMove = MoveDirection::UP;
    } else {
        usort(
            $possibleMoves,
            static fn ($a, $b): bool => $a->foodDistance <=> $b->foodDistance
        );
        $nextMove = $possibleMoves[0]->direction;
    }

    $shout = null;
    if (empty($possibleMoves) || (random_int(1, 100) > 90)) {
        $shout = (new ExceptionGenerator())->randomMessage();
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
