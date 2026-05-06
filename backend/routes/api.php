<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarriageController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\RelationshipController;
use App\Http\Middleware\EnsureIsAdmin;
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
    Route::get('/people',    [PersonController::class, 'index']);
    Route::get('/people/{id}', [PersonController::class, 'show']);
    Route::get('/marriages/{personId}', [MarriageController::class, 'getByPerson']);
    Route::post('/relationships/check', [RelationshipController::class, 'check']);

    // Admin-only write routes
    Route::middleware(EnsureIsAdmin::class)->group(function () {
        Route::post('/people',           [PersonController::class, 'store']);
        Route::put('/people/{id}',       [PersonController::class, 'update']);
        Route::patch('/people/{id}',     [PersonController::class, 'update']);
        Route::post('/people/{id}',      [PersonController::class, 'update']); // _method=PUT
        Route::delete('/people/{id}',    [PersonController::class, 'destroy']);

        Route::post('/marriages',        [MarriageController::class, 'store']);
        Route::delete('/marriages/{id}', [MarriageController::class, 'destroy']);
    });
//});
