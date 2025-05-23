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
        usort($possibleMoves, static function ($a, $b) {
            return $a->foodDistance <=> $b->foodDistance;
        });
        $nextMove = $possibleMoves[0]->direction;
    }

    $shout = null;
    if (empty($possibleMoves) || (random_int(1, 100) > 90)) {
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
