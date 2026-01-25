<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Enums\MessageType;
use Illuminate\Contracts\Support\Arrayable;

final readonly class ParsedMessage implements Arrayable
{
    public function __construct(
        public string $from,
        public MessageType $type,
        public string $body
    ) {
    }

    public function toArray(): array
    {
        $arr = get_object_vars($this);
        $arr['type'] = $this->type->value;

        return $arr;
    }
}
