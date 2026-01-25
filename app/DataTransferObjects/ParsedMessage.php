<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

final readonly class ParsedMessage implements Arrayable
{
    public function __construct(
        public string $from,
        public string $type,
        public string $body
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
