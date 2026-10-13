<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/report', [ReportController::class, 'store'])
    ->middleware(['throttle:6,1', 'profile.complete'])
    ->name('report.store');
