<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Public share route
Route::get('/share/{token}/tree', [PersonController::class, 'publicTree']);

// Authenticated routes
//Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout',                  [AuthController::class, 'logout']);
    Route::get('/auth/me',                       [AuthController::class, 'me']);
    Route::post('/auth/change-password',         [AuthController::class, 'changePassword']);
    Route::post('/auth/regenerate-share-token',  [AuthController::class, 'regenerateShareToken']);

    Route::get('/tree',      [PersonController::class, 'roots']);
    Route::get('/tree/{id}', [PersonController::class, 'tree']);
    Route::apiResource('people', PersonController::class);
//});
