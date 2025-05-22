<?php

namespace App\Battlesnake;

use Crell\Serde\Attributes as Serde;

#[Serde\ClassSettings(omitNullFields: true)]
class SnakeResponseDetails
{
    public function __construct(
        public string $apiversion,
        public ?string $author = null,
        public ?string $color = null,
        public ?string $head = null,
        public ?string $tail = null,
        public ?string $version = null
    ) {
    }
}
