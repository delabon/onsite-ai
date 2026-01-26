<?php

declare(strict_types=1);

namespace App\Services\Whatsapp\Handlers;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use Illuminate\Support\Facades\Log;

final readonly class MaterialRequestHandler implements WorkflowHandler
{
    public function handle(ParsedMessage $message, ClassificationResult $classification): void
    {
        $workflow = [
            'workflow' => [
                'action' => 'forward_to_procurement',
                'priority' => 'normal',
                'notify' => ['procurement_team'],
                'create_ticket' => true,
            ],
            'message' => $message->toArray(),
            'classification' => $classification->toArray(),
        ];

        $this->log('Forwarding to procurement', $workflow);
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
