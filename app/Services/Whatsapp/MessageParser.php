<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use InvalidArgumentException;

final class MessageParser
{
    public function parse(array $payload): array
    {
        $entry = $payload['entry'][0] ?? null;

        if (! $entry) {
            throw new InvalidArgumentException('Invalid payload: missing entry');
        }

        $change = $entry['changes'][0] ?? null;

        if (! $change) {
            throw new InvalidArgumentException('Invalid payload: missing changes');
        }

        $value = $change['value'] ?? null;

        if (! $value) {
            throw new InvalidArgumentException('Invalid payload: missing value');
        }

        $messages = $value['messages'] ?? [];
        $message = $messages[0] ?? null;

        if (empty($message)) {
            throw new InvalidArgumentException('Invalid payload: missing messages');
        }

        $from = $message['from'] ?? '';
        $type = $message['type'] ?? '';
        $body = '';

        if ($type === 'text' && isset($message['text']['body'])) {
            $body = trim($message['text']['body']);

            if (empty($body)) {
                throw new InvalidArgumentException('Invalid payload: empty message body');
            }
        }

        return [
            'from' => $from,
            'type' => $type,
            'body' => $body,
        ];
    }
}
