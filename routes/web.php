<?php

use App\BattlesnakeApi\Enum\MoveDirection;
use App\BattlesnakeApi\Request\SnakeRequestEnd;
use App\BattlesnakeApi\Request\SnakeRequestMove;
use App\BattlesnakeApi\Request\SnakeRequestStart;
use App\BattlesnakeApi\Response\SnakeResponseDetails;
use App\BattlesnakeApi\Response\SnakeResponseMove;
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

Route::post('/move', function (Request $request, SerdeCommon $serde) {
    $data = $serde->deserialize($request->getContent(), 'json', SnakeRequestMove::class);

    $board = $data->board;
    $throwableSnake = $data->you;

    $possibleMoves = array_filter(
        $throwableSnake->possibleMoves($board),
        static fn (PossibleMove $move): bool => !$move->isOutOfBounds()
    );

    $possibleMoves = array_filter(
        $possibleMoves,
        static fn (PossibleMove $move): bool => !$move->collidesWithAnySnake()
    );

    foreach ($board->snakes as $snake) {
        if ($snake->id === $throwableSnake->id) {
            continue;
        }

        $possibleMoves = array_filter(
            $possibleMoves,
            static fn (PossibleMove $move): bool =>
                !$move->isAdjacentToSnakeHead($snake) || $throwableSnake->length > $snake->length
        );
    }

    $healthThreshold = config('snake.health_threshold');
    $closestFoodDistance = !empty($possibleMoves)
        ? min(array_map(static fn(PossibleMove $m): int => $m->foodDistance(), $possibleMoves))
        : 0;

    $enemySnakes = array_filter(
        $board->snakes,
        static fn(Battlesnake $s): bool => $s->id !== $throwableSnake->id
    );
    $isShortest = !empty($enemySnakes) && empty(array_filter(
        $enemySnakes,
        static fn(Battlesnake $s): bool => $s->length <= $throwableSnake->length
    ));

    $needsFood = $isShortest || ($throwableSnake->health - $closestFoodDistance) < $healthThreshold;

    $safeMoves = array_filter(
        $possibleMoves,
        static fn(PossibleMove $m): bool => $m->floodFill() >= $throwableSnake->length
    );
    if (!empty($safeMoves)) {
        $possibleMoves = $safeMoves;
    }

    usort(
        $possibleMoves,
        static function (PossibleMove $a, PossibleMove $b) use ($needsFood): int {
            if ($a->isKillingMove() !== $b->isKillingMove()) {
                return $b->isKillingMove() <=> $a->isKillingMove();
            }
            if ($needsFood) {
                return $a->foodDistance() <=> $b->foodDistance();
            }
            if ($a->huntDistance() !== $b->huntDistance()) {
                return $a->huntDistance() <=> $b->huntDistance();
            }
            if ($a->floodFill() !== $b->floodFill()) {
                return $b->floodFill() <=> $a->floodFill();
            }
            return $b->voronoiTerritory() <=> $a->voronoiTerritory();
        }
    );

    $noPossibleMoves = empty($possibleMoves);

    return response(
        $serde->serialize(new SnakeResponseMove(
            move: $noPossibleMoves ? MoveDirection::UP : $possibleMoves[0]->direction,
            shout: ($noPossibleMoves || (random_int(1, 100) > 90)) ? (new ExceptionGenerator)->randomMessage() : null,
        ), 'json'),
        200,
        ['Content-Type' => 'application/json']
    );
});

Route::post('/start', function (Request $request, SerdeCommon $serde) {
    $serde->deserialize($request->getContent(), 'json', SnakeRequestStart::class);
    return response()->noContent();
});

Route::post('/end', function (Request $request, SerdeCommon $serde) {
    $serde->deserialize($request->getContent(), 'json', SnakeRequestEnd::class);
    return response()->noContent();
});
