<?php

declare(strict_types=1);

use App\Jobs\Whatsapp\ProcessMessage;
use App\Repositories\MessageRepository;
use App\Services\Whatsapp\MessageClassifier;
use App\Services\Whatsapp\MessageParser;
use App\Services\Whatsapp\WorkflowRouter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('processes message successfully', function () {
    Http::fake([
        config('services.ollama.url').'/api/generate' => Http::response([
            'response' => '{"category":"question","confidence":"high","reason":"test"}',
        ], 200),
    ]);

    $payload = json_decode(file_get_contents(__DIR__.'/../../../Payloads/Whatsapp/text-message.json'), true);

    $job = new ProcessMessage($payload);

    Log::shouldReceive('info')->with('Message stored', Mockery::any())->once();
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Routing to AI agent with RAG', Mockery::any())->once();
    Log::shouldReceive('info')->with('WhatsApp message processed successfully', [
        'payload' => $payload,
    ])->once();

    $job->handle(
        new MessageParser,
        new MessageClassifier,
        new MessageRepository,
        app(WorkflowRouter::class)
    );
});

it('handles parsing failure', function () {
    $invalidPayload = ['invalid' => 'data'];
    $job = new ProcessMessage($invalidPayload);

    Log::shouldReceive('warning')->once()->with('Invalid WhatsApp payload in job', [
        'error' => 'Invalid payload: missing entry',
        'payload' => $invalidPayload,
    ]);

    $job->handle(
        new MessageParser,
        new MessageClassifier,
        new MessageRepository,
        app(WorkflowRouter::class)
    );
});
