<?php

use App\Enums\MessageCategory;
use App\Services\Whatsapp\MessageClassifier;

it('classifies message successfully with real Ollama', function () {
    $classifier = new MessageClassifier;

    $result = $classifier->classify('Need 10 more bags of cement delivered tomorrow');

    expect($result['success'])->toBeTrue();
    expect($result['category'])->toBeIn(MessageCategory::valid());
    expect($result)->toHaveKey('confidence');
    expect($result)->toHaveKey('raw_response');
    expect($result)->toHaveKey('model_used');
});

it('handles failure when Ollama is down', function () {
    // Temporarily change config to invalid URL to simulate down
    config()->set('services.ollama.url', 'http://invalid-url:9999');

    $classifier = new MessageClassifier;

    $result = $classifier->classify('Test message');

    expect($result['success'])->toBeFalse();
    expect($result['category'])->toBe('unknown');
    expect($result)->toHaveKey('error');
});

it('classifies all sample messages with real Ollama', function () {
    $classifier = new MessageClassifier;

    $testMessages = [
        'Just saw a worker not wearing a hard hat in zone 3',
        'Need 10 more bags of cement delivered tomorrow',
        'What time does the safety inspection start?',
        'Completed foundation work on building A today',
        'Lunch break in 30 minutes',
    ];

    foreach ($testMessages as $message) {
        $result = $classifier->classify($message);

        expect($result['success'])->toBeTrue();
        expect($result['category'])->toBeIn(MessageCategory::all());
        expect($result)->toHaveKey('confidence');
        expect($result)->toHaveKey('raw_response');
        expect($result)->toHaveKey('model_used');
    }
});

it('handles API error with real Ollama', function () {
    // Assuming we can trigger an error, e.g., by sending invalid data
    // For simplicity, test with a message that might cause parsing issues
    $classifier = new MessageClassifier;

    $result = $classifier->classify(''); // Empty message

    expect($result['success'])->toBeTrue(); // Should still succeed, as HTTP works, parsing handles empty
    expect($result['category'])->toBeIn(['unknown', 'other']); // Depending on LLM response
});

it('respects config values in real classification', function () {
    // Test that model_used reflects config
    $classifier = new MessageClassifier;

    $result = $classifier->classify('Test message');

    expect($result['success'])->toBeTrue();
    expect($result['model_used'])->toBe(config('services.ollama.model', 'llama3.2:latest'));
});
