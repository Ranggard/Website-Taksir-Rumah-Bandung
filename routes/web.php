<?php

use App\Http\Controllers\PageController as P;
use App\Http\Middleware\DummyAuth;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [P::class, 'home'])->name('home');

// Hanya tamu (user login akan dialihkan ke Beranda)
Route::middleware(DummyAuth::class . ':guest')->group(function () {
    Route::get('/login', [P::class, 'loginForm'])->name('login');
    Route::post('/login', [P::class, 'loginStore'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [P::class, 'registerForm'])->name('register');
    Route::post('/register', [P::class, 'registerStore'])->name('register.store');
    Route::get('/lupa-password', [P::class, 'forgotForm'])->name('forgot');
    Route::post('/lupa-password/selesai', [P::class, 'forgotDone'])->name('forgot.done');
});

// Hanya user login (tamu dialihkan ke Login)
Route::middleware(DummyAuth::class . ':auth')->group(function () {
    Route::get('/prediksi', [P::class, 'predictForm'])->name('predict');
    Route::post('/prediksi', [P::class, 'predictStore'])->name('predict.store');
    Route::get('/hasil/{id}', [P::class, 'result'])->name('result');
    Route::get('/riwayat', [P::class, 'history'])->name('history');
    Route::delete('/riwayat/{id}', [P::class, 'historyDestroy'])->name('history.destroy');
    Route::get('/peta', [P::class, 'map'])->name('map');
    Route::get('/profil', [P::class, 'profile'])->name('profile');
    Route::put('/profil', [P::class, 'profileUpdate'])->name('profile.update');
    Route::post('/logout', [P::class, 'logout'])->name('logout');
});
