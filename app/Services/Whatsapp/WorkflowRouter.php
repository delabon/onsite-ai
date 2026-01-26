<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\MessageCategory;
use Illuminate\Support\Facades\Log;

final class WorkflowRouter
{
    /**
     * Route message to appropriate workflow based on classification
     */
    public function route(ParsedMessage $parsedMessage, ClassificationResult $classification): void
    {
        $category = $classification->category;

        match ($category) {
            MessageCategory::SafetyIncident => $this->notifySupervisorUrgent([
                'workflow' => [
                    'action' => 'notify_supervisor_urgent',
                    'priority' => 'critical',
                    'notify' => ['supervisor', 'safety_officer'],
                    'create_ticket' => true,
                ],
                'message' => $parsedMessage->toArray(),
                'classification' => $classification->toArray(),
            ]),
            MessageCategory::MaterialRequest => $this->forwardToProcurement([
                'workflow' => [
                    'action' => 'forward_to_procurement',
                    'priority' => 'normal',
                    'notify' => ['procurement_team'],
                    'create_ticket' => true,
                ],
                'message' => $parsedMessage->toArray(),
                'classification' => $classification->toArray(),
            ]),
            MessageCategory::Question => $this->routeToAiAgent([
                'workflow' => [
                    'action' => 'route_to_ai_agent',
                    'priority' => 'normal',
                    'use_rag' => true,
                    'auto_respond' => true,
                ],
                'message' => $parsedMessage->toArray(),
                'classification' => $classification->toArray(),
            ]),
            MessageCategory::SiteNote => $this->logToTimeline([
                'workflow' => [
                    'action' => 'log_to_timeline',
                    'priority' => 'low',
                    'create_timeline_entry' => true,
                ],
                'message' => $parsedMessage->toArray(),
                'classification' => $classification->toArray(),
            ]),
            default => $this->manualReview([
                'workflow' => [
                    'action' => 'manual_review',
                    'priority' => 'low',
                ],
                'message' => $parsedMessage->toArray(),
                'classification' => $classification->toArray(),
            ])
        };
    }

    private function notifySupervisorUrgent(array $workflow): void
    {
        $this->log('Notifying supervisor urgently', $workflow);
        // TODO: Dispatch notification job or event (e.g., NotifySupervisorUrgent::dispatch($workflow))
    }

    private function routeToAiAgent(array $workflow): void
    {
        $this->log('Routing to AI agent with RAG', $workflow);
        // TODO: Query RAG system or dispatch AiAgentQuery::dispatch($workflow)
    }

    private function forwardToProcurement(array $workflow): void
    {
        $this->log('Forwarding to procurement', $workflow);
        // TODO: Create ticket or dispatch ProcurementRequest::dispatch($workflow)
    }

    private function logToTimeline(array $workflow): void
    {
        $this->log('Logging to timeline', $workflow);
        // TODO: Create timeline entry via model/service
    }

    private function manualReview(array $workflow): void
    {
        $this->log('Manual review required for workflow', $workflow);
        // TODO: Manual review
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
