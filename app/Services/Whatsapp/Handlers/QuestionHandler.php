<?php

declare(strict_types=1);

namespace App\Services\Whatsapp\Handlers;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final readonly class QuestionHandler implements WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void
    {
        $workflow = [
            'workflow' => [
                'action' => 'route_to_ai_agent',
                'priority' => 'normal',
                'use_rag' => true,
                'auto_respond' => true,
            ],
            'message' => $message->toArray(),
            'classification' => $classification->toArray(),
        ];

        $this->log('Routing to AI agent with RAG', $workflow);
    }

    private function log(string $message, array $workflow): void
    {
        Log::info('Workflow routed', [
            'category' => $workflow['classification']['category'],
            'action' => $workflow['workflow']['action'],
            'from' => $workflow['message']['from'],
        ]);
        Log::info($message, $workflow);
    }
}
