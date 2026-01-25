<?php

use App\DataTransferObjects\ParsedMessage;
use App\Enums\MessageType;
use App\Services\Whatsapp\WorkflowRouter;

it('routes safety incident to urgent notification workflow', function () {
    $router = new WorkflowRouter;

    $classification = [
        'category' => 'safety_incident',
        'confidence' => 'high',
    ];

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Worker injured'
    );

    $result = $router->route($classification, $message);

    expect($result['workflow']['action'])->toBe('notify_supervisor_urgent');
    expect($result['workflow']['priority'])->toBe('critical');
    expect($result['workflow']['create_ticket'])->toBeTrue();
});

it('routes question to AI agent with RAG', function () {
    $router = new WorkflowRouter;

    $classification = ['category' => 'question'];

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'What PPE is required?'
    );

    $result = $router->route($classification, $message);

    expect($result['workflow']['action'])->toBe('route_to_ai_agent');
    expect($result['workflow']['use_rag'])->toBeTrue();
});

it('routes material request to procurement workflow', function () {
    $router = new WorkflowRouter;

    $classification = ['category' => 'material_request'];

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Need more cement'
    );

    $result = $router->route($classification, $message);

    expect($result['workflow']['action'])->toBe('forward_to_procurement');
    expect($result['workflow']['priority'])->toBe('normal');
    expect($result['workflow']['create_ticket'])->toBeTrue();
});

it('routes site note to timeline logging workflow', function () {
    $router = new WorkflowRouter;

    $classification = ['category' => 'site_note'];

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Site is clean today'
    );

    $result = $router->route($classification, $message);

    expect($result['workflow']['action'])->toBe('log_to_timeline');
    expect($result['workflow']['priority'])->toBe('low');
    expect($result['workflow']['create_timeline_entry'])->toBeTrue();
});

it('routes unknown category to manual review', function () {
    $router = new WorkflowRouter;

    $classification = ['category' => 'unknown'];

    $message = new ParsedMessage(
        from: '353861234567',
        type: MessageType::Text,
        body: 'Some message'
    );

    $result = $router->route($classification, $message);

    expect($result['workflow']['action'])->toBe('manual_review');
    expect($result['workflow']['priority'])->toBe('low');
});
