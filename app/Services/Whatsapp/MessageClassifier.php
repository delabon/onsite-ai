<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\DataTransferObjects\ClassificationResult;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MessageClassifier
{
    private string $ollamaUrl;

    private string $ollamaLlm;

    private int $timeout;

    private float $temperature;

    private int $responseLength;

    public function __construct()
    {
        $this->ollamaUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->ollamaLlm = config('services.ollama.model', 'llama3.2:latest');
        $this->timeout = config('services.ollama.timeout', 30);
        $this->temperature = config('services.ollama.temperature', 0.1);
        $this->responseLength = config('services.ollama.response_length', 50);
    }

    /**
     * Classify a WhatsApp message into predefined categories
     */
    public function classify(string $message): ClassificationResult
    {
        $prompt = $this->buildClassificationPrompt($message);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->ollamaUrl}/api/generate", [
                    'model' => $this->ollamaLlm,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => [
                        'temperature' => $this->temperature,
                        'num_predict' => $this->responseLength,
                    ],
                ]);

            if (! $response->successful()) {
                throw new Exception('Ollama API error: '.$response->status());
            }

            $result = $response->json();
            $classification = $this->parseClassification($result['response'] ?? '');

            return new ClassificationResult(
                success: true,
                category: MessageCategory::from($classification['category']),
                confidence: Confidence::from($classification['confidence']),
                rawResponse: $result['response'],
                modelUsed: $this->ollamaLlm
            );
        } catch (Exception $e) {
            Log::error('Classification failed: '.$e->getMessage());

            return new ClassificationResult(
                success: false,
                category: MessageCategory::Unknown,
                confidence: Confidence::Unknown,
                error: $e->getMessage(),
            );
        }
    }

    /**
     * Build a structured prompt for classification
     */
    private function buildClassificationPrompt(string $message): string
    {
        $categoriesList = '';
        $i = 1;
        foreach (MessageCategory::cases() as $category) {
            $categoriesList .= "{$i}. {$category->value} - {$category->descriptionForAi()}\n";
            $i++;
        }

        return <<<PROMPT
You are a construction site message classifier. Analyze the following message from a construction worker and classify it into ONE of these categories:

CATEGORIES:
{$categoriesList}
MESSAGE: "{$message}"

Respond STRICTLY in this JSON format:
{
    "category": "one_of_the_categories_above",
    "confidence": "high/medium/low",
    "reason": "brief_explanation"
}

Only output the JSON, nothing else.
PROMPT;
    }

    /**
     * Parse and validate the LLM response
     */
    private function parseClassification(string $llmResponse): array
    {
        // Try to extract JSON from the response
        $jsonPattern = '/\{[^}]+\}/';
        preg_match($jsonPattern, $llmResponse, $matches);

        if (empty($matches)) {
            return [
                'category' => MessageCategory::Unknown->value,
                'confidence' => 'low',
                'reason' => 'Failed to parse LLM response',
            ];
        }

        try {
            $data = json_decode($matches[0], true);

            $validCategories = MessageCategory::valid();

            $category = $data['category'] ?? MessageCategory::Unknown->value;

            if (! in_array($category, $validCategories)) {
                $category = MessageCategory::Other->value;
            }

            return [
                'category' => $category,
                'confidence' => $data['confidence'] ?? 'medium',
                'reason' => $data['reason'] ?? 'No reason provided',
            ];

        } catch (Exception $e) {
            return [
                'category' => MessageCategory::Unknown->value,
                'confidence' => 'low',
                'reason' => 'JSON parsing failed',
            ];
        }
    }
}
