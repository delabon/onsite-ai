<?php

declare(strict_types=1);

use App\Http\Controllers\Whatsapp\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->group(function () {
    Route::post('/whatsapp', [WebhookController::class, 'handleIncoming']);
});
