<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MuzakkiController;
use App\Http\Controllers\MuzakkiAccountController;
use App\Http\Controllers\MustahikController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZakatAgreementController;
use App\Http\Controllers\TagihanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ——————————————————————————————————
// Public: Halaman Utama (tanpa auth)
// ——————————————————————————————————
Route::post('/donasi', [DonasiController::class, 'store']);
Route::get('/public/program', [ProgramController::class, 'publicList']);
Route::get('/public/laporan', [LaporanController::class, 'publicReport']);
Route::get('/public/berita', [BeritaController::class, 'publicList']);
Route::get('/public/berita/{idOrSlug}', [BeritaController::class, 'publicDetail']);
Route::get('/public/muzakki', [MuzakkiController::class, 'publicList']);
Route::post('/public/muzakki/register', [MuzakkiController::class, 'publicRegister']);
Route::post('/public/zakat-request', [ZakatAgreementController::class, 'publicStore']);

// Muzakki Account Management (public)
Route::post('/muzakki/create-account', [MuzakkiAccountController::class, 'createAccount']);
Route::post('/muzakki/login', [MuzakkiAccountController::class, 'login']);



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

    // Muzakki Account Management (protected)
    Route::post('/muzakki/set-password', [MuzakkiAccountController::class, 'setPassword']);

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
    // Berita Routes (Admin)
    // ——————————————————————————
    Route::post('/berita/upload-image', [BeritaController::class, 'uploadImage']);
    Route::get('/berita',               [BeritaController::class, 'index']);
    Route::post('/berita',              [BeritaController::class, 'store']);
    Route::get('/berita/{berita}',      [BeritaController::class, 'show']);
    Route::put('/berita/{berita}',      [BeritaController::class, 'update']);
    Route::delete('/berita/{berita}',   [BeritaController::class, 'destroy']);

    // ——————————————————————————
    // Manajemen Pengguna (Admin only)
    // ——————————————————————————
    Route::get('/users',             [UserController::class, 'index']);
    Route::post('/users',            [UserController::class, 'store']);
    Route::put('/users/{user}',      [UserController::class, 'update']);
    Route::delete('/users/{user}',   [UserController::class, 'destroy']);

    // ——————————————————————————
    // Manajemen Permintaan Perubahan Kesepakatan Zakat
    // ——————————————————————————
    Route::get('/zakat-requests',                         [ZakatAgreementController::class, 'index']);
    Route::get('/zakat-requests/pending-count',           [ZakatAgreementController::class, 'pendingCount']);
    Route::patch('/zakat-requests/{id}/approve',          [ZakatAgreementController::class, 'approve']);
    Route::patch('/zakat-requests/{id}/reject',           [ZakatAgreementController::class, 'reject']);

    // ——————————————————————————
    // Manajemen Tagihan & Kepatuhan Zakat
    // ——————————————————————————
    Route::get('/tagihan',                                [TagihanController::class, 'index']);
    Route::post('/tagihan/catat-bayar',                   [TagihanController::class, 'catatBayar']);
});

// Route publik untuk serve file storage via /api/storage/
Route::get('/storage/{folder}/{file}', function ($folder, $file) {
    $path = storage_path('app/public/' . $folder . '/' . $file);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->where('file', '.*');
