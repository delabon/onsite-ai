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
            $workflow = $router->route($classification, $parsedMessage);
            $this->executeWorkflow($workflow);

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

    private function executeWorkflow(array $workflow): void
    {
        $action = $workflow['workflow']['action'];

        match ($action) {
            'notify_supervisor_urgent' => $this->notifySupervisorUrgent($workflow),
            'route_to_ai_agent' => $this->routeToAiAgent($workflow),
            'forward_to_procurement' => $this->forwardToProcurement($workflow),
            'log_to_timeline' => $this->logToTimeline($workflow),
            default => Log::info('Manual review required for workflow', $workflow),
        };
    }

    private function notifySupervisorUrgent(array $workflow): void
    {
        Log::info('Notifying supervisor urgently', $workflow['workflow']);
        // TODO: Dispatch notification job or event (e.g., NotifySupervisorUrgent::dispatch($workflow))
    }

    private function routeToAiAgent(array $workflow): void
    {
        Log::info('Routing to AI agent with RAG', $workflow['workflow']);
        // TODO: Query RAG system or dispatch AiAgentQuery::dispatch($workflow)
    }

    private function forwardToProcurement(array $workflow): void
    {
        Log::info('Forwarding to procurement', $workflow['workflow']);
        // TODO: Create ticket or dispatch ProcurementRequest::dispatch($workflow)
    }

    private function logToTimeline(array $workflow): void
    {
        Log::info('Logging to timeline', $workflow['workflow']);
        // TODO: Create timeline entry via model/service
    }
}
