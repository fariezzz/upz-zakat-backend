<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
 * Route bernama "login" — wajib ada karena Laravel/Sanctum mencari
 * route ini saat AuthenticationException dilempar.
 * Karena backend ini API-only, kembalikan JSON 401 bukan redirect.
 */
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthenticated. Silakan login melalui aplikasi.',
    ], 401);
})->name('login');

/*
 * Route sementara untuk menjalankan Migrate & Seed di cPanel
 * Buka: https://api.upz.unsil.ac.id/init-migrate-seed
 * (Hapus route ini setelah selesai inisialisasi database)
 */
Route::get('/init-migrate-seed', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Database berhasil dimigrasi dan di-seed!',
            'output' => $output,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
