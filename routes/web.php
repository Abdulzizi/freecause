<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'en|fr|it');

Route::redirect('/', '/en');

Route::group([
    'prefix' => '{locale}',
    'middleware' => 'setLocale'
], function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/', HomeController::class)->name('home');

    Route::get('/petition/{slug}/{id}', function ($locale, $slug, $id) {
        return view('petition.demo_show', compact('locale', 'slug', 'id'));
    });

    Route::get('/petitions', function () {
        return view('pages.petitions');
    });

    Route::get('/faqs', function () {
        return view('pages.faq');
    });


    Route::get(
        '/petitions/category-{categorySlug}-{category}',
        [CategoryPetitionController::class, 'index']
    )->where([
        'categorySlug' => '[a-z0-9\-]+',
        'category' => '[0-9]+',
    ])->name('petitions.byCategory');
});
