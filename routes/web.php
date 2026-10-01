<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';


Route::get('login/social/{id}', [AuthController::class, 'redirectToProvider']);
Route::get('login/social/callback/{id}', [AuthController::class, 'handleProviderCallback']);

Route::get('/user', function (Request $request) {
    return [
        'mensaje' => 'Bienvenido ' . $request->user()->name,
        'datos' => $request->user()
    ];
})->middleware('auth');
