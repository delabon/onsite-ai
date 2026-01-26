<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

final readonly class WorkflowContext implements Arrayable
{
    public function __construct(
        public ParsedMessage $message,
        public ClassificationResult $classification
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message->toArray(),
            'classification' => $this->classification->toArray(),
        ];
    }
}
