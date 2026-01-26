<?php

declare(strict_types=1);

namespace App\Services\Whatsapp\Handlers;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final readonly class ManualReviewHandler implements WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void
    {
        $workflow = [
            'workflow' => [
                'action' => 'manual_review',
                'priority' => 'low',
            ],
            'message' => $message->toArray(),
            'classification' => $classification->toArray(),
        ];

        $this->log('Manual review required for workflow', $workflow);
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
