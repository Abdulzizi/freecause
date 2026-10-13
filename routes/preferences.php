<?php

use App\Http\Controllers\PreferencesController;
use Illuminate\Support\Facades\Route;

Route::get('/preferences', [PreferencesController::class, 'show'])
    ->name('preferences.show');

Route::post('/preferences', [PreferencesController::class, 'update'])
    ->middleware('throttle:10,1')
    ->name('preferences.update');

Route::get('/unsubscribe/{token}', [PreferencesController::class, 'unsubscribe'])
    ->name('preferences.unsubscribe');
