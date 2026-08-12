<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MuzakkiController;
use App\Http\Controllers\TransaksiController;
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

    // ——————————————————————————
    // Muzakki Routes
    // ——————————————————————————
    Route::get('/muzakki/options',     [MuzakkiController::class, 'options']); // combobox
    Route::get('/muzakki',             [MuzakkiController::class, 'index']);
    Route::post('/muzakki',            [MuzakkiController::class, 'store']);
    Route::put('/muzakki/{muzakki}',   [MuzakkiController::class, 'update']);
    Route::delete('/muzakki/{muzakki}',[MuzakkiController::class, 'destroy']);

    // ——————————————————————————
    // Transaksi Routes
    // ——————————————————————————
    Route::get('/transaksi/pengumpulan',  [TransaksiController::class, 'indexPengumpulan']);
    Route::post('/transaksi/pengumpulan', [TransaksiController::class, 'storePengumpulan']);
    Route::get('/transaksi/penyaluran',   [TransaksiController::class, 'indexPenyaluran']);
    Route::post('/transaksi/penyaluran',  [TransaksiController::class, 'storePenyaluran']);
});
