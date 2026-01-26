<?php

declare(strict_types=1);

use App\DataTransferObjects\ParsedMessage;
use App\Enums\MessageType;
use App\Services\Whatsapp\MessageParser;

it('parses a text message correctly', function () {
    $parser = new MessageParser();

    $payload = json_decode(
        json: file_get_contents(__DIR__.'/../../../Payloads/Whatsapp/text-message.json'),
        associative: true
    );

    $parsedMessage = $parser->parse($payload);

    expect($parsedMessage)->toBeInstanceOf(ParsedMessage::class)
        ->and($parsedMessage->from)->toBe('353861234567')
        ->and($parsedMessage->type)->toBe(MessageType::Text)
        ->and($parsedMessage->body)->toBe('Worker fell from scaffolding at Site A');
});

test('to array', function () {
    $parser = new MessageParser();

    $payload = json_decode(
        json: file_get_contents(__DIR__.'/../../../Payloads/Whatsapp/text-message.json'),
        associative: true
    );

    $parsedMessage = $parser->parse($payload)->toArray();

    expect($parsedMessage)->toBeArray()
        ->toHaveKey('from')
        ->toHaveKey('type')
        ->toHaveKey('body')
        ->and($parsedMessage['from'])->toBe('353861234567')
        ->and($parsedMessage['type'])->toBe(MessageType::Text->value)
        ->and($parsedMessage['body'])->toBe('Worker fell from scaffolding at Site A');
});

it('throws exception for missing entry', function () {
    $parser = new MessageParser;

    $payload = [];

    expect(fn () => $parser->parse($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid payload: missing entry');
});

it('throws exception for missing changes', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [],
            ],
        ],
    ];

    expect(fn () => $parser->parse($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid payload: missing changes');
});

it('throws exception for missing value', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'field' => 'messages',
                    ],
                ],
            ],
        ],
    ];

    expect(fn () => $parser->parse($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid payload: missing value');
});

it('throws exception for missing messages', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'field' => 'messages',
                        ],
                    ],
                ],
            ],
        ],
    ];

    expect(fn () => $parser->parse($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid payload: missing messages');
});

it('throws exception for empty message body', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '123',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => '   ', // whitespace only
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    expect(fn () => $parser->parse($payload))
        ->toThrow(InvalidArgumentException::class, 'Invalid payload: empty message body');
});

it('parses message with whitespace in body', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '123',
                                    'type' => 'text',
                                    'text' => [
                                        'body' => '  Hello World  ',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $result = $parser->parse($payload);

    expect($result->from)->toBe('123')
        ->and($result->type)->toBe(MessageType::Text)
        ->and($result->body)->toBe('Hello World');
});

it('handles missing optional fields', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => [
                                        'body' => 'Test message',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $result = $parser->parse($payload);

    expect($result->from)->toBe('')
        ->and($result->type)->toBe(MessageType::Text)
        ->and($result->body)->toBe('Test message');
});

it('throws exception for invalid type', function () {
    $parser = new MessageParser;

    $payload = [
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '123',
                                    'type' => 'invalid-type here',
                                    'text' => [
                                        'body' => 'Hello World',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    expect(static fn () => $parser->parse($payload))
        ->toThrow(ValueError::class);
});
