<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final class MessageRepository
{
    public function store(ParsedMessage $parsedMessage, ClassificationResult $classification): void
    {
        // TODO: Save to database

        // For now, just log
        Log::info('Message stored', [
            'from' => $parsedMessage->from,
            'type' => $parsedMessage->type->value,
            'message' => $parsedMessage->body,
            'category' => $classification->category->value,
        ]);
    }
}
