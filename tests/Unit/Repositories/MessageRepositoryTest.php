<?php

declare(strict_types=1);

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Log;

it('stores message and logs correctly', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353861234567',
            'type' => MessageType::Text->value,
            'message' => 'Worker fell from scaffolding at Site A',
            'category' => MessageCategory::SafetyIncident->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Worker fell from scaffolding at Site A'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SafetyIncident,
        confidence: Confidence::High
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('stores material request message', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353869876543',
            'type' => MessageType::Text->value,
            'message' => 'Need more cement for the foundation',
            'category' => MessageCategory::MaterialRequest->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353869876543',
        type: MessageType::Text,
        body: 'Need more cement for the foundation'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::MaterialRequest,
        confidence: Confidence::Medium
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('stores question message', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353865551234',
            'type' => MessageType::Text->value,
            'message' => 'What time does the meeting start?',
            'category' => MessageCategory::Question->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353865551234',
        type: MessageType::Text,
        body: 'What time does the meeting start?'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Question,
        confidence: Confidence::High
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('stores site note message', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353861112223',
            'type' => MessageType::Text->value,
            'message' => 'Progress update: Foundation completed today',
            'category' => MessageCategory::SiteNote->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353861112223',
        type: MessageType::Text,
        body: 'Progress update: Foundation completed today'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SiteNote,
        confidence: Confidence::Low
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('stores other category message', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353869998887',
            'type' => MessageType::Text->value,
            'message' => 'Some random message',
            'category' => MessageCategory::Other->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353869998887',
        type: MessageType::Text,
        body: 'Some random message'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Other,
        confidence: Confidence::Medium
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('stores unknown category message', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353864445566',
            'type' => MessageType::Text->value,
            'message' => 'Unclear message content',
            'category' => MessageCategory::Unknown->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353864445566',
        type: MessageType::Text,
        body: 'Unclear message content'
    );

    $classification = new ClassificationResult(
        success: false,
        category: MessageCategory::Unknown,
        confidence: Confidence::Unknown
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('handles empty from phone number', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '',
            'type' => MessageType::Text->value,
            'message' => 'Test message',
            'category' => MessageCategory::Question->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '',
        type: MessageType::Text,
        body: 'Test message'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Question,
        confidence: Confidence::High
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('handles special characters in message body', function () {
    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353861234567',
            'type' => MessageType::Text->value,
            'message' => 'Need materials: cement, steel, & glass!',
            'category' => MessageCategory::MaterialRequest->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Need materials: cement, steel, & glass!'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::MaterialRequest,
        confidence: Confidence::High
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});

it('handles long message body', function () {
    $longMessage = str_repeat('This is a very long message. ', 50);

    Log::shouldReceive('info')
        ->once()
        ->with('Message stored', [
            'from' => '353861234567',
            'type' => MessageType::Text->value,
            'message' => $longMessage,
            'category' => MessageCategory::SiteNote->value,
        ]);

    $parsedMessage = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: $longMessage
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SiteNote,
        confidence: Confidence::High
    );

    $repository = new MessageRepository;
    $repository->store($parsedMessage, $classification);
});
