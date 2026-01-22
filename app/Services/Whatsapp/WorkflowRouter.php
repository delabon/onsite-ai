<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\Enums\MessageCategory;
use Illuminate\Support\Facades\Log;

final class WorkflowRouter
{
    /**
     * Route message to appropriate workflow based on classification
     */
    public function route(array $classification, array $message): array
    {
        $category = $classification['category'];

        $workflow = match ($category) {
            MessageCategory::SafetyIncident->value => [
                'action' => 'notify_supervisor_urgent',
                'priority' => 'critical',
                'notify' => ['supervisor', 'safety_officer'],
                'create_ticket' => true,
            ],
            MessageCategory::MaterialRequest->value => [
                'action' => 'forward_to_procurement',
                'priority' => 'normal',
                'notify' => ['procurement_team'],
                'create_ticket' => true,
            ],
            MessageCategory::Question->value => [
                'action' => 'route_to_ai_agent',
                'priority' => 'normal',
                'use_rag' => true, // Would use your RAG system
                'auto_respond' => true,
            ],
            MessageCategory::SiteNote->value => [
                'action' => 'log_to_timeline',
                'priority' => 'low',
                'create_timeline_entry' => true,
            ],
            default => [
                'action' => 'manual_review',
                'priority' => 'low',
            ]
        };

        Log::info('Workflow routed', [
            'category' => $category,
            'action' => $workflow['action'],
            'from' => $message['from'],
        ]);

        return [
            'workflow' => $workflow,
            'message' => $message,
            'classification' => $classification,
        ];
    }
}
