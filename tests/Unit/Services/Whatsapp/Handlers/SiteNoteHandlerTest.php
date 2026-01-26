<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\Handlers\SiteNoteHandler;

it('handles site note correctly', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Logging to timeline', Mockery::any())->once();

    $handler = new SiteNoteHandler;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Site is clean today'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SiteNote,
        confidence: Confidence::High
    );

    $handler->handle($message, $classification);
});
