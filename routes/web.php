<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AI\AnomalyDetectionController;
use App\Http\Controllers\AI\ClassificationController;
use App\Http\Controllers\AI\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('api/ai')->middleware(['auth'])->group(function () {
    // Anomaly Detection
    Route::post('/detect-anomaly', [AnomalyDetectionController::class, 'detect'])->name('ai.detect-anomaly');
    Route::get('/anomaly/{id}', [AnomalyDetectionController::class, 'show'])->name('ai.anomaly.show');
    Route::get('/batch/{batchId}/anomalies', [AnomalyDetectionController::class, 'listByBatch'])->name('ai.batch.anomalies');
    Route::get('/batch/{batchId}/anomaly-stats', [AnomalyDetectionController::class, 'batchStats'])->name('ai.batch.anomaly-stats');

    // Plant Classification
    Route::post('/classify-plant', [ClassificationController::class, 'classify'])->name('ai.classify-plant');
    Route::get('/classification/{id}', [ClassificationController::class, 'show'])->name('ai.classification.show');
    Route::get('/batch/{batchId}/classifications', [ClassificationController::class, 'listByBatch'])->name('ai.batch.classifications');
    Route::get('/batch/{batchId}/classification-stats', [ClassificationController::class, 'batchStats'])->name('ai.batch.classification-stats');

    // AI Chatbot
    Route::post('/chat', [ChatbotController::class, 'chat'])->name('ai.chat');
    Route::post('/chat/stream', [ChatbotController::class, 'streamChat'])->name('ai.chat.stream');
    Route::get('/chat/history', [ChatbotController::class, 'history'])->name('ai.chat.history');
    Route::post('/chat/{chatId}/feedback', [ChatbotController::class, 'feedback'])->name('ai.chat.feedback');
    Route::get('/chat/stats', [ChatbotController::class, 'stats'])->name('ai.chat.stats');
});

require __DIR__.'/auth.php';
