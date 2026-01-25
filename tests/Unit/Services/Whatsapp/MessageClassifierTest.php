<?php

use App\DataTransferObjects\ClassificationResult;
use App\Enums\MessageCategory;
use App\Services\Whatsapp\MessageClassifier;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('constructs with custom config values', function () {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'custom-model',
        'timeout' => 30,
        'temperature' => 0.1,
        'response_length' => 50,
    ]);

    $classifier = new MessageClassifier;

    // Test indirectly by checking classify behavior
    Http::fake([
        'localhost:11434/api/generate' => Http::response(['response' => '{"category":"'.MessageCategory::SiteNote->value.'","confidence":"high","reason":"Test"}'], 200),
    ]);

    $result = $classifier->classify('test message');

    expect($result->success)->toBeTrue()
        ->and($result->modelUsed)->toBe('custom-model');
});

it('builds classification prompt correctly', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('buildClassificationPrompt');

    $prompt = $method->invoke($classifier, 'Test message');

    expect($prompt)->toContain('Test message')
        ->and($prompt)->toContain(MessageCategory::SafetyIncident->value)
        ->and($prompt)->toContain(MessageCategory::MaterialRequest->value)
        ->and($prompt)->toContain(MessageCategory::Question->value)
        ->and($prompt)->toContain(MessageCategory::SiteNote->value)
        ->and($prompt)->toContain(MessageCategory::Other->value)
        ->and($prompt)->toContain(MessageCategory::Unknown->value)
        ->and($prompt)->toContain('"category": "one_of_the_categories_above"');
});

it('parses valid classification response', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('parseClassification');

    $response = '{"category":"'.MessageCategory::SafetyIncident->value.'","confidence":"high","reason":"Test reason"}';
    $result = $method->invoke($classifier, $response);

    expect($result)->toBe([
        'category' => MessageCategory::SafetyIncident->value,
        'confidence' => 'high',
        'reason' => 'Test reason',
    ]);
});

it('parses partial classification response', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('parseClassification');

    $response = '{"category":"'.MessageCategory::Question->value.'"}';
    $result = $method->invoke($classifier, $response);

    expect($result)->toBe([
        'category' => MessageCategory::Question->value,
        'confidence' => 'medium',
        'reason' => 'No reason provided',
    ]);
});

it('parses invalid category in response', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('parseClassification');

    $response = '{"category":"invalid","confidence":"low"}';
    $result = $method->invoke($classifier, $response);

    expect($result['category'])->toBe('other');
});

it('handles non-JSON response', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('parseClassification');

    $response = 'Not JSON at all';
    $result = $method->invoke($classifier, $response);

    expect($result)->toBe([
        'category' => 'unknown',
        'confidence' => 'low',
        'reason' => 'Failed to parse LLM response',
    ]);
});

it('handles malformed JSON response', function () {
    $classifier = new MessageClassifier;
    $reflection = new ReflectionClass($classifier);
    $method = $reflection->getMethod('parseClassification');

    $response = '{"invalid": json}';
    $result = $method->invoke($classifier, $response);

    expect($result)->toBe([
        'category' => 'other',
        'confidence' => 'medium',
        'reason' => 'No reason provided',
    ]);
});

it('classifies message successfully', function () {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'llama3.2:latest',
        'timeout' => 30,
        'temperature' => 0.1,
        'response_length' => 50,
    ]);

    $classifier = new MessageClassifier;
    $rawResponse = '{"category":"'.MessageCategory::MaterialRequest->value.'","confidence":"high","reason":"Needs supplies"}';

    Http::fake([
        'localhost:11434/api/generate' => Http::response([
            'response' => $rawResponse,
        ], 200),
    ]);

    $result = $classifier->classify('Need more cement');

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeTrue()
        ->and($result->category)->toBe(MessageCategory::MaterialRequest)
        ->and($result->confidence)->toBe('high')
        ->and($result->rawResponse)->toBe($rawResponse)
        ->and($result->modelUsed)->toBe('llama3.2:latest');
});

it('handles HTTP failure in classification', function () {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'llama3.2:latest',
        'timeout' => 30,
    ]);

    $classifier = new MessageClassifier;

    Log::spy();

    Http::fake([
        'localhost:11434/api/generate' => Http::response('Error', 500),
    ]);

    $result = $classifier->classify('Test message');

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeFalse()
        ->and($result->category)->toBe(MessageCategory::Unknown)
        ->and($result->confidence)->toBe('unknown')
        ->and($result->error)->toBe('Ollama API error: 500');

    Log::shouldHaveReceived('error')->once();
});

it('handles exception in classification', function () {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'llama3.2:latest',
        'timeout' => 30,
    ]);

    $classifier = new MessageClassifier;

    Log::spy();

    Http::fake([
        'localhost:11434/api/generate' => Http::response(['response' => '{invalid'], 200),
    ]);

    $result = $classifier->classify('Test message');

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeTrue()
        ->and($result->category)->toBe(MessageCategory::Unknown)
        ->and($result->confidence)->toBe('low');
});

it('handles empty message', function () {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'llama3.2:latest',
        'timeout' => 30,
    ]);

    $classifier = new MessageClassifier;

    Http::fake([
        'localhost:11434/api/generate' => Http::response(['response' => '{"category":"other"}'], 200),
    ]);

    $result = $classifier->classify('');

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeTrue()
        ->and($result->category)->toBe(MessageCategory::Other)
        ->and($result->confidence)->toBe('medium');
});

dataset('testMessages', [
    MessageCategory::SafetyIncident->value => ['Just saw a worker not wearing a hard hat in zone 3', MessageCategory::SafetyIncident],
    MessageCategory::MaterialRequest->value => ['Need 10 more bags of cement delivered tomorrow', MessageCategory::MaterialRequest],
    MessageCategory::Question->value => ['What time does the safety inspection start?', MessageCategory::Question],
    MessageCategory::SiteNote->value => ['Completed foundation work on building A today', MessageCategory::SiteNote],
    MessageCategory::Other->value => ['Lunch break in 30 minutes', MessageCategory::Other],
]);

it('classifies sample messages', function ($message, $expectedCategory) {
    Config::set('services.ollama', [
        'url' => 'http://localhost:11434',
        'model' => 'llama3.2:latest',
        'timeout' => 30,
    ]);

    $classifier = new MessageClassifier;

    // Mock response based on message, but since real LLM, for unit we mock to expected
    $mockResponse = '{"category":"'.$expectedCategory->value.'","confidence":"high","reason":"Test"}';

    Http::fake([
        'localhost:11434/api/generate' => Http::response(['response' => $mockResponse], 200),
    ]);

    $result = $classifier->classify($message);

    expect($result)->toBeInstanceOf(ClassificationResult::class)
        ->and($result->success)->toBeTrue()
        ->and($result->category)->toBe($expectedCategory);
})->with('testMessages');
