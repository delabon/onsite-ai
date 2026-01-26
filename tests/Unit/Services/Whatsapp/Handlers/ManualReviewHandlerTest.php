<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\Handlers\ManualReviewHandler;

it('handles unknown category correctly', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Manual review required for workflow', Mockery::any())->once();

    $handler = new ManualReviewHandler;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Some message'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Unknown,
        confidence: Confidence::High
    );

    $handler->handle($message, $classification);
});
