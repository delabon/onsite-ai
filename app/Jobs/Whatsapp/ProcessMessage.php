<?php

declare(strict_types=1);

namespace App\Jobs\Whatsapp;

use App\Services\Whatsapp\MessageClassifier;
use App\Services\Whatsapp\MessageHandler;
use App\Services\Whatsapp\MessageParser;
use App\Services\Whatsapp\WorkflowRouter;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class ProcessMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public array $payload
    ) {}

    /**
     * @throws Exception
     */
    public function handle(
        MessageParser $parser,
        MessageClassifier $classifier,
        MessageHandler $handler,
        WorkflowRouter $router
    ): void {
        try {
            $parsedMessage = $parser->parse($this->payload);
            $classification = $classifier->classify($parsedMessage->body);
            $handler->storeMessage($parsedMessage, $classification);
            $router->route($parsedMessage, $classification);

            Log::info('WhatsApp message processed successfully', [
                'payload' => $this->payload,
            ]);
        } catch (InvalidArgumentException $e) {
            Log::warning('Invalid WhatsApp payload in job', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);
        } catch (Exception $e) {
            Log::error('Error processing WhatsApp message', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }
}
