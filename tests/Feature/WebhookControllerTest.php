<?php

use App\Jobs\Whatsapp\ProcessMessage;
use Illuminate\Support\Facades\Queue;

it('accepts valid whatsapp payload and queues processing', function () {
    Queue::fake();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => 'WHATSAPP_BUSINESS_ACCOUNT_ID',
                'changes' => [
                    [
                        'value' => [
                            'messaging_product' => 'whatsapp',
                            'messages' => [
                                [
                                    'from' => '353861234567',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => 'Hello, need more cement',
                                    ],
                                ],
                            ],
                        ],
                        'field' => 'messages',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/webhooks/whatsapp', $payload);

    $response->assertStatus(202)
        ->assertJson(['status' => 'processing']);

    Queue::assertPushed(ProcessMessage::class, function ($job) use ($payload) {
        return $job->payload === $payload;
    });
});

it('rejects invalid payload', function () {
    Queue::fake();

    $payload = ['invalid' => 'data'];

    $response = $this->postJson('/webhooks/whatsapp', $payload);

    $response->assertStatus(400)
        ->assertJson(['error' => 'Invalid payload: missing entry']);

    Queue::assertNotPushed(ProcessMessage::class);
});

it('rejects empty message', function () {
    Queue::fake();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => 'WHATSAPP_BUSINESS_ACCOUNT_ID',
                'changes' => [
                    [
                        'value' => [
                            'messaging_product' => 'whatsapp',
                            'messages' => [
                                [
                                    'from' => '353861234567',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => '',
                                    ],
                                ],
                            ],
                        ],
                        'field' => 'messages',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/webhooks/whatsapp', $payload);

    $response->assertStatus(400)
        ->assertJson(['error' => 'Invalid payload: empty message body']);

    Queue::assertNotPushed(ProcessMessage::class);
});

it('rejects whitespace only message', function () {
    Queue::fake();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'id' => 'WHATSAPP_BUSINESS_ACCOUNT_ID',
                'changes' => [
                    [
                        'value' => [
                            'messaging_product' => 'whatsapp',
                            'messages' => [
                                [
                                    'from' => '353861234567',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => '   ',
                                    ],
                                ],
                            ],
                        ],
                        'field' => 'messages',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/webhooks/whatsapp', $payload);

    $response->assertStatus(400)
        ->assertJson(['error' => 'Invalid payload: empty message body']);

    Queue::assertNotPushed(ProcessMessage::class);
});
