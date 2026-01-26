<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\Handlers\SafetyIncidentHandler;

it('handles safety incident correctly', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Notifying supervisor urgently', Mockery::any())->once();

    $handler = new SafetyIncidentHandler;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Worker injured'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SafetyIncident,
        confidence: Confidence::High
    );

    $handler->handle($message, $classification);
});
