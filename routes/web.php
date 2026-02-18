<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoriesController;
use App\Http\Controllers\Admin\AdminFanpagesController;
use App\Http\Controllers\Admin\AdminLanguagesController;
use App\Http\Controllers\Admin\AdminLogsController;
use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\Admin\AdminPetitionsController;
use App\Http\Controllers\Admin\AdminSignaturesController;
use App\Http\Controllers\Admin\AdminSpamController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminUsersController;

use App\Http\Controllers\Admin\AdsTxtController;
use App\Http\Controllers\Admin\GlobalOptionsController;
use App\Http\Controllers\Admin\LanguageOptionsController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetitionController;

use App\Support\Settings;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    $default = cache()->remember(
        'default_language',
        60,
        fn() => \App\Models\Language::where('is_default', 1)->value('code') ?? 'en'
    );

    return redirect("/{$default}");
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/options/global', [GlobalOptionsController::class, 'edit'])->name('options.global');
        Route::post('/options/global', [GlobalOptionsController::class, 'update'])->name('options.global.update');

        Route::get('/options/language', [LanguageOptionsController::class, 'edit'])->name('options.language');
        Route::post('/options/language', [LanguageOptionsController::class, 'update'])->name('options.language.update');

        Route::get('/ads', [AdsTxtController::class, 'edit'])->name('ads');
        Route::post('/ads', [AdsTxtController::class, 'update'])->name('ads.update');
        Route::get('/ads.txt', function () {
            $content = Settings::get('ads_txt', '', 'global');

            return response($content, 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        });

        Route::get('/users', [AdminUsersController::class, 'index'])->name('users');
        Route::post('/users/save', [AdminUsersController::class, 'save'])->name('users.save');
        Route::post('/users/bulk-banned', [AdminUsersController::class, 'bulkBan'])->name('users.bulkBan');
        Route::post('/users/bulk-action', [AdminUsersController::class, 'bulkAction'])->name('users.bulkAction');
        Route::post('/users/bulk-unban', [AdminUsersController::class, 'bulkUnban'])->name('users.bulkUnban');
        Route::post('/users/bulk-delete', [AdminUsersController::class, 'bulkDelete'])->name('users.bulkDelete');

        Route::get('/petitions', [AdminPetitionsController::class, 'index'])->name('petitions');
        Route::post('/petitions/save', [AdminPetitionsController::class, 'save'])->name('petitions.save');
        Route::post('/petitions/bulk-action', [AdminPetitionsController::class, 'bulkAction'])->name('petitions.bulkAction');

        Route::get('/categories', [AdminCategoriesController::class, 'index'])->name('categories');
        Route::post('/categories/save', [AdminCategoriesController::class, 'save'])->name('categories.save');

        Route::get('/fanpages', [AdminFanpagesController::class, 'index'])->name('fanpages');

        Route::get('/signatures', [AdminSignaturesController::class, 'index'])->name('signatures');
        Route::post('/signatures/bulk-delete', [AdminSignaturesController::class, 'bulkDelete'])->name('signatures.bulkDelete');

        Route::get('/pages', [AdminPagesController::class, 'index'])->name('pages');
        Route::post('/pages/save', [AdminPagesController::class, 'save'])->name('pages.save');
        Route::post('/pages/bulk-action', [AdminPagesController::class, 'bulkAction'])->name('pages.bulkAction');

        Route::get('/spam', [AdminSpamController::class, 'index'])->name('spam');

        Route::get('/stats', [AdminStatsController::class, 'index'])->name('stats');

        Route::get('/logs', [AdminLogsController::class, 'index'])->name('logs');
        Route::post('/logs/bulk-delete', [AdminLogsController::class, 'bulkDelete'])->name('logs.bulkDelete');

        Route::get('/languages', [AdminLanguagesController::class, 'index'])->name('languages.index');
        Route::post('/languages', [AdminLanguagesController::class, 'store'])->name('languages.store');
        Route::put('/languages/{language}', [AdminLanguagesController::class, 'update'])->name('languages.update');
        Route::post('/languages/{language}/default', [AdminLanguagesController::class, 'setDefault'])->name('languages.default');
        Route::delete('/languages/{language}', [AdminLanguagesController::class, 'destroy'])->name('languages.destroy');

        // TODO: remove these placeholder routes and create real pages for them
        Route::view('/system/user-info', 'admin.placeholders.system-user-info')->name('system.user_info');
        Route::view('/system/user-levels', 'admin.placeholders.system-user-levels')->name('system.user_levels');
        Route::view('/system/permissions', 'admin.placeholders.system-permissions')->name('system.permissions');

        Route::view('/utils/import', 'admin.placeholders.import')->name('utils.import');
    });
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setLocale', 'block.banned.user'],
], function () {
    // Auth + OAuth
    Route::get('/oauth/google', [GoogleAuthController::class, 'redirect'])->name('oauth.google');
    Route::get('/oauth/google/callback', [GoogleAuthController::class, 'callback'])->name('oauth.google.callback');

    Route::get('/oauth/facebook', [FacebookAuthController::class, 'redirect'])->name('oauth.facebook');
    Route::get('/oauth/facebook/callback', [FacebookAuthController::class, 'callback'])->name('oauth.facebook.callback');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/verify/{token}', [AuthController::class, 'verify'])->name('verify.account');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('account.profile.update');
        Route::post('/account/delete', [AuthController::class, 'delete'])->name('account.delete');
        Route::get('/my-petitions', [PetitionController::class, 'myPetitions'])->name('account.petitions');

        Route::post('/create-petition', [PetitionController::class, 'store'])->name('petition.store');
    });

    // Static pages
    Route::get('/magazine', fn() => view('pages.magazine'))->name('magazine');
    Route::get('/faqs', fn() => view('pages.faq'))->name('faqs');
    Route::get('/contacts', fn() => view('pages.contacts'))->name('contacts');

    Route::post('/contacts', function () {
        return back()->with('success', 'Thanks! (UI only for now)');
    })->name('contacts.submit');

    // Home + Petitions
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/petitions', [PetitionController::class, 'index'])->name('petitions.index');
    Route::get('/petitions/category-{categorySlug}-{category}', [CategoryPetitionController::class, 'index'])
        ->where([
            'categorySlug' => '[a-z0-9\-]+',
            'category' => '[0-9]+',
        ])->name('petitions.byCategory');

    // Petition creation
    Route::get('/create-petition', [PetitionController::class, 'create'])->name('petition.create');

    // Petition actions (show / sign / thanks)
    Route::get('/petition/{slug}/{id}', [PetitionController::class, 'show'])->where(['id' => '[0-9]+'])->name('petition.show');
    Route::get('/petition/{slug}/{id}/sign', [PetitionController::class, 'signPage'])->where(['id' => '[0-9]+'])->name('petition.sign.page');
    Route::post('/petition/{slug}/{id}/sign', [PetitionController::class, 'sign'])->where(['id' => '[0-9]+'])->name('petition.sign');
    Route::get('/petition/{slug}/{id}/thanksforsigning/{status?}', [PetitionController::class, 'thanks'])->where('id', '[0-9]+')->where('status', '([0-9]+|created)?')->name('petition.thanks');

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

    Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('page.show');
});
