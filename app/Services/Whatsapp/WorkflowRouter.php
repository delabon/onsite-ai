<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\Contracts\WorkflowHandler;
use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use RuntimeException;

final readonly class WorkflowRouter
{
    public function __construct(
        private array $handlers = []
    ) {}

    public function route(ParsedMessage $message, ClassificationResult $classification): void
    {
        $category = $classification->category->value;
        /** @var WorkflowHandler $handler */
        $handler = $this->handlers[$category] ?? throw new RuntimeException("No handler for category: {$category}");

        $handler->handle($message, $classification);
    }
}
