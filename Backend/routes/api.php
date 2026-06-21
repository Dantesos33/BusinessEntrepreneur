<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollaborationRequestController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── Public auth endpoints ──────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// ── Authenticated endpoints (Sanctum SPA cookie session) ───────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/investors', [UserController::class, 'investors']);
    Route::get('/entrepreneurs', [UserController::class, 'entrepreneurs']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}', [UserController::class, 'update']);

    Route::get('/collaboration-requests', [CollaborationRequestController::class, 'index']);
    Route::post('/collaboration-requests', [CollaborationRequestController::class, 'store']);
    Route::patch('/collaboration-requests/{id}', [CollaborationRequestController::class, 'update']);

    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/{userId}', [MessageController::class, 'show']);
    Route::post('/messages', [MessageController::class, 'store']);

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

    Route::get('/deals', [DealController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
});
