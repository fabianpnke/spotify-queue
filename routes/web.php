<?php

use App\Http\Controllers\SpotifyAuthController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', Dashboard::class)
    ->name('dashboard')
    ->middleware(['auth']);

Route::get('/auth/redirect', [SpotifyAuthController::class, 'redirect'])
    ->name('auth.redirect')
    ->middleware(['guest']);

Route::get('/auth/callback', [SpotifyAuthController::class, 'callback'])
    ->name('auth.callback')
    ->middleware(['guest']);
