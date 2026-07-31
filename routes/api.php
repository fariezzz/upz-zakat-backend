<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ——————————————————————————————————
// Auth Routes (public)
// ——————————————————————————————————
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ——————————————————————————————————
// Auth Routes (protected)
// ——————————————————————————————————
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',     [AuthController::class, 'me']);
    });

    // ——————————————————————————————
    // Dashboard Routes
    // ——————————————————————————————
    Route::prefix('dashboard')->group(function () {
        Route::get('/all',            [DashboardController::class, 'all']);
        Route::get('/stats',          [DashboardController::class, 'stats']);
        Route::get('/ringkasan-dana', [DashboardController::class, 'ringkasanDana']);
        Route::get('/grafik',         [DashboardController::class, 'grafik']);
    });

    // Transaksi & Program (standalone endpoints)
    Route::get('/transaksi', [DashboardController::class, 'transaksiTerbaru']);
    Route::get('/program',   [DashboardController::class, 'programAktif']);
});
