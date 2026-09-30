<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';


Route::get('login/social/{id}', [AuthController::class, 'redirectToProvider']);
Route::get('login/social/callback/{id}', [AuthController::class, 'handleProviderCallback']);
