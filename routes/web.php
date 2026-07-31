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
