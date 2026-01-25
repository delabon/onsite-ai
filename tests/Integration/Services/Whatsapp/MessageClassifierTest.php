<?php

use App\DataTransferObjects\ClassificationResult;
use App\Enums\MessageCategory;
use App\Services\Whatsapp\MessageClassifier;

it('classifies message successfully with real Ollama', function () {
    $classifier = new MessageClassifier;

    $result = $classifier->classify('Need 10 more bags of cement delivered tomorrow');

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeTrue()
        ->and($result->category)->toBe(MessageCategory::MaterialRequest)
        ->and($result->confidence)->toBe('high')
        ->and($result->rawResponse)->toBeString()
        ->and($result->modelUsed)->toBe(config('services.ollama.model'));
});

it('handles failure when Ollama is down', function () {
    // Temporarily change config to invalid URL to simulate down
    config()->set('services.ollama.url', 'http://invalid-url:9999');

    $classifier = new MessageClassifier;

    $result = $classifier->classify('Test message');

    expect($result->success)->toBeFalse()
        ->and($result->category)->toBe(MessageCategory::Unknown)
        ->and($result->confidence)->toBe('unknown')
        ->and($result->error)->not()->toBeEmpty();
});

it('classifies all sample messages with real Ollama', function () {
    $classifier = new MessageClassifier;

    $testMessages = [
        'Just saw a worker not wearing a hard hat in zone 3' => [
            'category' => MessageCategory::SafetyIncident,
            'confidence' => 'high',
        ],
        'Need 10 more bags of cement delivered tomorrow' => [
            'category' => MessageCategory::MaterialRequest,
            'confidence' => 'high',
        ],
        'What time does the safety inspection start?' => [
            'category' => MessageCategory::Question,
            'confidence' => 'high',
        ],
        'Completed foundation work on building A today' => [
            'category' => MessageCategory::SiteNote,
            'confidence' => 'high',
        ],
        'Lunch break in 30 minutes' => [
            'category' => MessageCategory::SiteNote,
            'confidence' => 'high',
        ],
    ];

    foreach ($testMessages as $message => $messageData) {
        $result = $classifier->classify($message);

        expect($result->success)->toBeTrue()
            ->and($result->category)->toBe($messageData['category'])
            ->and($result->confidence)->toBe($messageData['confidence']);
    }
});

it('handles API error with real Ollama', function () {
    // Assuming we can trigger an error, e.g., by sending invalid data
    // For simplicity, test with a message that might cause parsing issues
    $classifier = new MessageClassifier;

    $result = $classifier->classify(''); // Empty message

    expect($result->success)->toBeTrue() // Should still succeed, as HTTP works, parsing handles empty
        ->and($result->category)->toBeIn([MessageCategory::Unknown, MessageCategory::Other]);
});

it('respects config values in real classification', function () {
    // Test that model_used reflects config
    $classifier = new MessageClassifier;

    $result = $classifier->classify('Test message');

    expect($result->success)->toBeTrue()
        ->and($result->modelUsed)->toBe(config('services.ollama.model', 'llama3.2:latest'));
});
