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

    foreach ($board->snakes as $snake) {
        if ($snake->id === $throwableSnake->id) {
            continue;
        }

        $possibleMoves = array_filter(
            $possibleMoves,
            static fn (PossibleMove $move): bool =>
                !$move->isAdjacentToSnakeHead($snake) || $throwableSnake->length > $snake->length
        );

        foreach ($possibleMoves as $move) {
            if ($move->isAdjacentToSnakeHead($snake)) {
                $move->isKillingMove = true;
            }
        }
    }

    /** @var PossibleMove $move */
    foreach ($possibleMoves as $move) {
        if (!empty($board->food)) {
            $move->foodDistance = min(
                array_map(
                    static fn(Coordinate $f): int => $move->position->distanceFrom($f),
                    $board->food
                )
            );
        }
    }

    $healthThreshold = config('snake.health_threshold');
    $closestFoodDistance = !empty($possibleMoves)
        ? min(array_map(static fn(PossibleMove $m): int => $m->foodDistance, $possibleMoves))
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

    $huntTarget = null;
    $minHuntDistance = PHP_INT_MAX;
    foreach ($board->snakes as $snake) {
        if ($snake->id === $throwableSnake->id || $snake->length >= $throwableSnake->length) {
            continue;
        }
        $distance = $throwableSnake->head->distanceFrom($snake->head);
        if ($distance < $minHuntDistance) {
            $minHuntDistance = $distance;
            $huntTarget = $snake;
        }
    }

    if ($huntTarget !== null) {
        foreach ($possibleMoves as $move) {
            $move->huntDistance = $move->position->distanceFrom($huntTarget->head);
        }
    }

    foreach ($possibleMoves as $move) {
        $move->spaceAvailable = $move->floodFill($board, $board->snakes);
    }

    $safeMoves = array_filter(
        $possibleMoves,
        static fn(PossibleMove $m): bool => $m->spaceAvailable >= $throwableSnake->length
    );
    if (!empty($safeMoves)) {
        $possibleMoves = $safeMoves;
    }

    usort(
        $possibleMoves,
        static function ($a, $b) use ($needsFood, $huntTarget): int {
            if ($a->isKillingMove !== $b->isKillingMove) {
                return $b->isKillingMove <=> $a->isKillingMove;
            }
            if ($needsFood) {
                return $a->foodDistance <=> $b->foodDistance;
            }
            if ($huntTarget !== null) {
                return $a->huntDistance <=> $b->huntDistance;
            }
            return $b->spaceAvailable <=> $a->spaceAvailable;
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
