<?php

declare(strict_types=1);

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Jobs\Whatsapp\ProcessMessage;
use App\Services\Whatsapp\MessageParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class WebhookController extends Controller
{
    public function __construct(
        private readonly MessageParser $messageParser
    ) {}

    /**
     * Handle incoming WhatsApp webhook
     */
    public function handleIncoming(Request $request): JsonResponse
    {
        // TODO: Validate webhook signature (implement based on your WhatsApp provider)

        $payload = json_decode($request->getContent(), true) ?? [];

        try {
            // Quick validation: ensure payload has basic structure
            $parsed = $this->messageParser->parse($payload);
        } catch (InvalidArgumentException $e) {
            Log::warning('Invalid WhatsApp payload', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Dispatch job for processing
        ProcessMessage::dispatch($payload)->onQueue('whatsapp');

        Log::info('WhatsApp message queued for processing');

        return response()->json([
            'status' => 'processing',
        ], Response::HTTP_ACCEPTED);
    }
}
