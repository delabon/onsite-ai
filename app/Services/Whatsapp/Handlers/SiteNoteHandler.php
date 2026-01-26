<?php

declare(strict_types=1);

namespace App\Services\Whatsapp\Handlers;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final readonly class SiteNoteHandler implements WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void
    {
        $workflow = [
            'workflow' => [
                'action' => 'log_to_timeline',
                'priority' => 'low',
                'create_timeline_entry' => true,
            ],
            'message' => $message->toArray(),
            'classification' => $classification->toArray(),
        ];

        $this->log('Logging to timeline', $workflow);
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
