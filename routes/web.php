<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoriesController;
use App\Http\Controllers\Admin\AdminFanpagesController;
use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\Admin\AdminPetitionsController;
use App\Http\Controllers\Admin\AdminSignaturesController;
use App\Http\Controllers\Admin\AdminUsersController;

use App\Http\Controllers\Admin\AdsTxtController;
use App\Http\Controllers\Admin\CountryOptionsController;
use App\Http\Controllers\Admin\GlobalOptionsController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetitionController;

use App\Support\Settings;

use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'en|fr|it');

Route::redirect('/', '/en');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.options.global'))->name('dashboard');

        Route::get('/options/global', [GlobalOptionsController::class, 'edit'])->name('options.global');
        Route::post('/options/global', [GlobalOptionsController::class, 'update'])->name('options.global.update');

        Route::get('/options/country', [CountryOptionsController::class, 'edit'])->name('options.country');
        Route::post('/options/country', [CountryOptionsController::class, 'update'])->name('options.country.update');

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

        Route::get('/petitions', [AdminPetitionsController::class, 'index'])->name('petitions');
        Route::post('/petitions/save', [AdminPetitionsController::class, 'save'])->name('petitions.save');
        Route::post('/petitions/bulk-banned', [AdminPetitionsController::class, 'bulkBan'])->name('petitions.bulkBan');
        Route::post('/petitions/bulk-action', [AdminPetitionsController::class, 'bulkAction'])->name('petitions.bulkAction');

        Route::get('/categories', [AdminCategoriesController::class, 'index'])->name('categories');
        Route::post('/categories/save', [AdminCategoriesController::class, 'save'])->name('categories.save');

        Route::get('/fanpages', [AdminFanpagesController::class, 'index'])->name('fanpages');

        Route::get('/signatures', [AdminSignaturesController::class, 'index'])->name('signatures');
        Route::post('/signatures/bulk-delete', [AdminSignaturesController::class, 'bulkDelete'])->name('signatures.bulkDelete');

        Route::get('/pages', [AdminPagesController::class, 'index'])->name('pages');
        Route::post('/pages/save', [AdminPagesController::class, 'save'])->name('pages.save');

        // parity placeholders (build screens next)
        Route::view('/spam', 'admin.placeholders.spam')->name('spam');
        Route::view('/stats', 'admin.placeholders.stats')->name('stats');
        Route::view('/logs', 'admin.placeholders.logs')->name('logs');

        Route::view('/system/user-info', 'admin.placeholders.system-user-info')->name('system.user_info');
        Route::view('/system/user-levels', 'admin.placeholders.system-user-levels')->name('system.user_levels');
        Route::view('/system/permissions', 'admin.placeholders.system-permissions')->name('system.permissions');

        Route::view('/utils/import', 'admin.placeholders.import')->name('utils.import');
    });
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => 'setLocale'
], function () {
    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('page.show');

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
    Route::get('/magazine', fn() => view('pages.magazine'))->name('magazine');
    Route::get('/faqs', fn() => view('pages.faq'))->name('faqs');
    Route::get('/terms-of-service', fn() => view('pages.terms-of-service'))->name('terms');
    Route::get('/ethical-code', fn() => view('pages.ethical-code'))->name('ethical-code');
    Route::get('/privacy-policy', fn() => view('pages.privacy-policy'))->name('privacy-policy');
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
        ])
        ->name('petitions.byCategory');

    // Petition creation
    Route::get('/create-petition', [PetitionController::class, 'create'])->name('petition.create');
    Route::post('/create-petition', [PetitionController::class, 'store'])
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
