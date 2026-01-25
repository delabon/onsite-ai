<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final class MessageHandler
{
    public function storeMessage(ParsedMessage $parsedMessage, ClassificationResult $classification): void
    {
        // TODO: Save to database

        // For now, just log
        Log::info('Message stored', [
            'from' => $parsedMessage->from,
            'type' => $parsedMessage->type,
            'message' => $parsedMessage->body,
            'category' => $classification->category->value,
        ]);
    }
}
