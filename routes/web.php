<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\PetitionCreateController;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'en|fr|it');

Route::redirect('/', '/en');

Route::group([
    'prefix' => '{locale}',
    'middleware' => 'setLocale'
], function () {

    // Auth + OAuth
    // centralized google oauth routes
    Route::get('/oauth/google', [GoogleAuthController::class, 'redirect'])->name('oauth.google');
    Route::get('/oauth/google/callback', [GoogleAuthController::class, 'callback'])->name('oauth.google.callback');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    // Account
    Route::get('/profile', [AuthController::class, 'profile'])
        ->middleware('auth')
        ->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])
        ->middleware('auth')
        ->name('account.profile.update');
    Route::post('/account/delete', [AuthController::class, 'delete'])
        ->middleware('auth')
        ->name('account.delete');
    Route::get('/my-petitions', [PetitionController::class, 'myPetitions'])
        ->middleware('auth')
        ->name('account.petitions');

    // Static pages
    Route::get('/magazine', function () {
        return view('pages.magazine');
    })->name('magazine');
    Route::get('/faqs', function () {
        return view('pages.faq');
    })->name('faqs');

    // Home + Petitions
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/petitions', [PetitionController::class, 'index'])->name('petitions.index');
    Route::get('/petitions/category-{categorySlug}-{category}', [CategoryPetitionController::class, 'index'])
        ->where([
            'categorySlug' => '[a-z0-9\-]+',
            'category' => '[0-9]+',
        ])
        ->name('petitions.byCategory');

    // Petition creation
    Route::get('/create-petition', [PetitionCreateController::class, 'create'])->name('petition.create');
    Route::post('/create-petition', [PetitionCreateController::class, 'store'])
        ->middleware('auth')
        ->name('petition.store');
    // Petition actions (show / sign / thanks)
    Route::get('/petition/{slug}/{id}', [PetitionController::class, 'show'])
        ->where(['id' => '[0-9]+'])
        ->name('petition.show');
    Route::get('/petition/{slug}/{id}/sign', [PetitionController::class, 'signPage'])
        ->where(['id' => '[0-9]+'])
        ->name('petition.sign.page');
    Route::post('/petition/{slug}/{id}/sign', [PetitionController::class, 'sign'])
        ->where(['id' => '[0-9]+'])
        ->name('petition.sign');
    Route::get('/petition/{slug}/{id}/thanksforsigning/{status?}', [PetitionController::class, 'thanks'])
        ->where('id', '[0-9]+')
        ->where('status', '([0-9]+|created)?')
        ->name('petition.thanks');

    // Petition owner operations
    Route::get('/petition/{slug}/{id}/edit', [PetitionController::class, 'edit'])
        ->middleware('auth')
        ->where(['id' => '[0-9]+'])
        ->name('petition.edit');

    Route::post('/petition/{slug}/{id}/edit', [PetitionController::class, 'update'])
        ->middleware('auth')
        ->where(['id' => '[0-9]+'])
        ->name('petition.update');

    Route::get('/petition/{slug}/{id}/download/txt', [PetitionController::class, 'downloadTxt'])
        ->middleware('auth')
        ->where(['id' => '[0-9]+'])
        ->name('petition.download.txt');

    Route::get('/petition/{slug}/{id}/download/pdf', [PetitionController::class, 'downloadPdf'])
        ->middleware('auth')
        ->where(['id' => '[0-9]+'])
        ->name('petition.download.pdf');
});
