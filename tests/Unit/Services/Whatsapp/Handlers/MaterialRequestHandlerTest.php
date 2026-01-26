<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\Handlers\MaterialRequestHandler;

it('handles material request correctly', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Forwarding to procurement', Mockery::any())->once();

    $handler = new MaterialRequestHandler;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Need more cement'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::MaterialRequest,
        confidence: Confidence::High
    );

    $handler->handle($message, $classification);
});
