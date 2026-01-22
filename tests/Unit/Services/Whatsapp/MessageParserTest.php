<?php

declare(strict_types=1);

use App\Services\Whatsapp\MessageParser;

it('parses a text message correctly', function () {
    $parser = new MessageParser;

    $payload = json_decode(
        json: file_get_contents(__DIR__.'/../../../Payloads/Whatsapp/text-message.json'),
        associative: true
    );

    $result = $parser->parse($payload);

    expect($result)->toBeArray()
        ->toHaveKey('from')
        ->toHaveKey('type')
        ->toHaveKey('body')
        ->and($result['from'])->toBe('353861234567')
        ->and($result['type'])->toBe('text')
        ->and($result['body'])->toBe('Worker fell from scaffolding at Site A');
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

    expect($result)->toBe([
        'from' => '123',
        'type' => 'text',
        'body' => 'Hello World',
    ]);
});

it('parses non-text message correctly', function () {
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
                                    'type' => 'image',
                                    'image' => [
                                        'id' => 'image_id',
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

    expect($result)->toBe([
        'from' => '123',
        'type' => 'image',
        'body' => '',
    ]);
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

    expect($result)->toBe([
        'from' => '',
        'type' => 'text',
        'body' => 'Test message',
    ]);
});
