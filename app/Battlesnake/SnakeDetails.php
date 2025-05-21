<?php

namespace App\Battlesnake;

use JsonSerializable;

class SnakeDetails implements JsonSerializable
{
    public function __construct(
        public string $apiversion,
        public ?string $author,
        public ?string $color,
        public ?string $head,
        public ?string $tail,
        public ?string $version
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'apiversion' => $this->apiversion,
            ...($this->author ? ['author' => $this->author] : []),
            ...($this->color ? ['color' => $this->color] : []),
            ...($this->head ? ['head' => $this->head] : []),
            ...($this->tail ? ['tail' => $this->tail] : []),
            ...($this->version ? ['version' => $this->version] : []),
        ];
    }
}
