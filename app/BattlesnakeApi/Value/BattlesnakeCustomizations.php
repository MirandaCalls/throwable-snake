<?php

namespace App\BattlesnakeApi\Value;

readonly class BattlesnakeCustomizations
{
    public function __construct(
        public string $color,
        public string $head,
        public string $tail,
    ) {
    }
}
