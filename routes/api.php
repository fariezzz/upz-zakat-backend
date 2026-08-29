<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MuzakkiController;
use App\Http\Controllers\MustahikController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ——————————————————————————————————
// Public: Halaman Utama (tanpa auth)
// ——————————————————————————————————
Route::post('/donasi', [DonasiController::class, 'store']);
Route::get('/public/program', [ProgramController::class, 'publicList']);
Route::get('/public/laporan', [LaporanController::class, 'publicReport']);


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
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::get('/me',               [AuthController::class, 'me']);
        Route::put('/profile',          [AuthController::class, 'updateProfile']);
        Route::put('/password',         [AuthController::class, 'updatePassword']);
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
    // Mustahik Routes
    // ——————————————————————————
    Route::get('/mustahik',              [MustahikController::class, 'index']);
    Route::post('/mustahik',             [MustahikController::class, 'store']);
    Route::put('/mustahik/{mustahik}',   [MustahikController::class, 'update']);
    Route::delete('/mustahik/{mustahik}',[MustahikController::class, 'destroy']);

    // ——————————————————————————
    // Program Routes
    // ——————————————————————————
    Route::get('/program/options',     [ProgramController::class, 'options']);
    Route::get('/program',             [ProgramController::class, 'index']);
    Route::post('/program',            [ProgramController::class, 'store']);
    Route::put('/program/{program}',   [ProgramController::class, 'update']);
    Route::delete('/program/{program}',[ProgramController::class, 'destroy']);

    // ——————————————————————————
    // Transaksi Routes
    // ——————————————————————————
    Route::get('/transaksi/pengumpulan',  [TransaksiController::class, 'indexPengumpulan']);
    Route::post('/transaksi/pengumpulan', [TransaksiController::class, 'storePengumpulan']);
    Route::get('/transaksi/penyaluran',   [TransaksiController::class, 'indexPenyaluran']);
    Route::post('/transaksi/penyaluran',  [TransaksiController::class, 'storePenyaluran']);
    Route::delete('/transaksi/{id}',      [TransaksiController::class, 'destroy']);

    // ——————————————————————————
    // Donasi Online (admin view)
    // ——————————————————————————
    Route::get('/donasi', [DonasiController::class, 'index']);

    // ——————————————————————————
    // Laporan Keuangan
    // ——————————————————————————
    Route::get('/laporan/ringkasan', [LaporanController::class, 'ringkasan']);

    // ——————————————————————————
    // Jurnal Akuntansi
    // ——————————————————————————
    Route::get('/jurnal',            [JurnalController::class, 'index']);
    Route::post('/jurnal',           [JurnalController::class, 'store']);
    Route::put('/jurnal/{jurnal}',   [JurnalController::class, 'update']);
    Route::delete('/jurnal/{jurnal}',[JurnalController::class, 'destroy']);

    // ——————————————————————————
    // Manajemen Pengguna (Admin only)
    // ——————————————————————————
    Route::get('/users',             [UserController::class, 'index']);
    Route::post('/users',            [UserController::class, 'store']);
    Route::put('/users/{user}',      [UserController::class, 'update']);
    Route::delete('/users/{user}',   [UserController::class, 'destroy']);
});
