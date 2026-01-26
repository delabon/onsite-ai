<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\Handlers\QuestionHandler;

it('handles question correctly', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Routing to AI agent with RAG', Mockery::any())->once();

    $handler = new QuestionHandler;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'What PPE is required?'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Question,
        confidence: Confidence::High
    );

    $handler->handle($message, $classification);
});
