<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\PetitionOauthController;
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

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/petitions', [PetitionController::class, 'index'])->name('petitions.index');

    Route::post('/petition/{slug}/{id}/sign', [PetitionController::class, 'sign'])
    ->name('petition.sign');

    Route::get('/petition/{slug}/{id}/thanksforsigning/{status?}', [PetitionController::class, 'thanks'])
        ->where('status', '[0-9]+')
        ->name('petition.thanks');

    Route::get('/petition/{slug}/{id}/oauth/google', [PetitionOauthController::class, 'redirect'])
        ->name('petition.oauth.google');

    Route::get('/oauth/google/callback', [PetitionOauthController::class, 'callback'])
        ->name('oauth.google.callback');

    Route::get('/petition/{slug}/{id}', [PetitionController::class, 'show'])
        ->where(['id' => '[0-9]+'])
        ->name('petition.show');

    Route::get('/magazine', function () {
        return view('pages.magazine'); // simple static page
    })->name('magazine');


    Route::get('/faqs', function () {
        return view('pages.faq');
})->name('faqs');

    Route::get('/petitions/category-{categorySlug}-{category}', [CategoryPetitionController::class, 'index'])->where(['categorySlug' => '[a-z0-9\-]+', 'category' => '[0-9]+',])->name('petitions.byCategory');
});
