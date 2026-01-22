<?php

use App\Http\Controllers\Whatsapp\WebhookController;
use App\Jobs\Whatsapp\ProcessMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;

it('handles valid payload and dispatches job', function () {
    Queue::fake();

    $controller = app(WebhookController::class);

    $payload = json_decode(file_get_contents(__DIR__.'/../../../../Payloads/Whatsapp/text-message.json'), true);
    $request = Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

    $response = $controller->handleIncoming($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_ACCEPTED);
    expect($response->getData())->toEqual((object) ['status' => 'processing']);

    Queue::assertPushed(ProcessMessage::class, function ($job) use ($payload) {
        return $job->payload === $payload;
    });
});

it('returns bad request for invalid payload', function () {
    Queue::fake();

    $controller = app(WebhookController::class);

    $payload = ['invalid' => 'data'];
    $request = Request::create('/', 'POST', [], [], [], [], json_encode($payload));

    $response = $controller->handleIncoming($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect($response->getData())->toEqual((object) ['error' => 'Invalid payload: missing entry']);

    Queue::assertNotPushed(ProcessMessage::class);
});

it('returns bad request for empty message', function () {
    Queue::fake();

    $controller = app(WebhookController::class);

    $payload = json_decode(file_get_contents(__DIR__.'/../../../../Payloads/Whatsapp/text-message.json'), true);
    $payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] = '';

    $request = Request::create('/', 'POST', [], [], [], [], json_encode($payload));

    $response = $controller->handleIncoming($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect($response->getData())->toEqual((object) ['error' => 'Invalid payload: empty message body']);

    Queue::assertNotPushed(ProcessMessage::class);
});

it('returns bad request for whitespace only message', function () {
    Queue::fake();

    $controller = app(WebhookController::class);

    $payload = json_decode(file_get_contents(__DIR__.'/../../../../Payloads/Whatsapp/text-message.json'), true);
    $payload['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] = '    ';

    $request = Request::create('/', 'POST', [], [], [], [], json_encode($payload));

    $response = $controller->handleIncoming($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST);
    expect($response->getData())->toEqual((object) ['error' => 'Invalid payload: empty message body']);

    Queue::assertNotPushed(ProcessMessage::class);
});
