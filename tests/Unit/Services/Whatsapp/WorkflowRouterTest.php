<?php

use App\DataTransferObjects\ClassificationResult;
use App\DataTransferObjects\ParsedMessage;
use App\Enums\Confidence;
use App\Enums\MessageCategory;
use App\Enums\MessageType;
use App\Services\Whatsapp\WorkflowRouter;

it('routes safety incident to urgent notification workflow', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Notifying supervisor urgently', Mockery::any())->once();

    $router = new WorkflowRouter;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Worker injured'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SafetyIncident,
        confidence: Confidence::High
    );

    $router->route($message, $classification);
});

it('routes question to AI agent with RAG', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Routing to AI agent with RAG', Mockery::any())->once();

    $router = new WorkflowRouter;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'What PPE is required?'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Question,
        confidence: Confidence::High
    );

    $router->route($message, $classification);
});

it('routes material request to procurement workflow', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Forwarding to procurement', Mockery::any())->once();

    $router = new WorkflowRouter;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Need more cement'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::MaterialRequest,
        confidence: Confidence::High
    );

    $router->route($message, $classification);
});

it('routes site note to timeline logging workflow', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Logging to timeline', Mockery::any())->once();

    $router = new WorkflowRouter;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Site is clean today'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::SiteNote,
        confidence: Confidence::High
    );

    $router->route($message, $classification);
});

it('routes unknown category to manual review', function () {
    Log::shouldReceive('info')->with('Workflow routed', Mockery::any())->once();
    Log::shouldReceive('info')->with('Manual review required for workflow', Mockery::any())->once();

    $router = new WorkflowRouter;

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Some message'
    );

    $classification = new ClassificationResult(
        success: true,
        category: MessageCategory::Unknown,
        confidence: Confidence::High
    );

    $router->route($message, $classification);
});
