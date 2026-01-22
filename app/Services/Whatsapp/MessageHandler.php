<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use Illuminate\Support\Facades\Log;

final class MessageHandler
{
    public function storeMessage(array $parsedMessage, array $classification): void
    {
        // TODO: Save to database

        // For now, just log
        Log::info('Message stored', [
            'from' => $parsedMessage['from'],
            'type' => $parsedMessage['type'],
            'message' => $parsedMessage['body'],
            'category' => $classification['category'],
        ]);
    }

    public function triggerWorkflow(array $parsedMessage, array $classification): void
    {
        // TODO: Trigger an action based on the classification in a queue

        // For now, just log
        Log::info('Workflow triggered', [
            'from' => $parsedMessage['from'],
            'type' => $parsedMessage['type'],
            'message' => $parsedMessage['body'],
            'category' => $classification['category'],
        ]);
    }
}
