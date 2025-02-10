<?php

use App\Http\ConsoleApi\Controller\ProjectController;
use Hyvor\Internal\Http\Middleware\AuthMiddleware;

/**
 * This is an internal API for user-level functions
 * This cannot be accessed via API keys
 * Used only in our Console
 */
Route::prefix('/console/v0')
    ->middleware([
        AuthMiddleware::class,
    ])
    ->group(function () {
        Route::post('/project', [ProjectController::class, 'createProject']);
    });

