<?php

declare(strict_types=1);

namespace App\Services\Whatsapp\Handlers;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final readonly class SafetyIncidentHandler implements WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void
    {
        $workflow = [
            'workflow' => [
                'action' => 'notify_supervisor_urgent',
                'priority' => 'critical',
                'notify' => ['supervisor', 'safety_officer'],
                'create_ticket' => true,
            ],
            'message' => $message->toArray(),
            'classification' => $classification->toArray(),
        ];

        $this->log('Notifying supervisor urgently', $workflow);
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
