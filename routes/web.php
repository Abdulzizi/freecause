<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoriesController;
// use App\Http\Controllers\Admin\AdminFanpagesController;
use App\Http\Controllers\Admin\AdminLanguagesController;
use App\Http\Controllers\Admin\AdminLogsController;
use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\Admin\AdminPetitionsController;
use App\Http\Controllers\Admin\AdminSignaturesController;
use App\Http\Controllers\Admin\AdminSpamController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminUserLevelsController;
use App\Http\Controllers\Admin\AdminUsersController;

use App\Http\Controllers\Admin\AdsTxtController;
use App\Http\Controllers\Admin\GlobalOptionsController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\LanguageOptionsController;
use App\Http\Controllers\Admin\System\PermissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryPetitionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\UserProfileController;
use App\Mail\ContactMail;
use App\Support\AppLog;
use App\Support\Settings;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    $default = cache()->remember(
        'default_language',
        60,
        fn() => \App\Models\Language::where('is_default', 1)->value('code') ?? 'en'
    );

    return redirect("/{$default}");
});

Route::prefix('admin')->name('admin.')->middleware('no.cache')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');

    Route::middleware(['admin.auth', 'admin.audit'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        //* OPTIONS
        Route::get('/options/global', [GlobalOptionsController::class, 'edit'])->middleware('permission:options,view')->name('options.global');
        Route::post('/options/global', [GlobalOptionsController::class, 'update'])->middleware('permission:options,edit')->name('options.global.update');
        Route::get('/options/language', [LanguageOptionsController::class, 'edit'])->middleware('permission:options,view')->name('options.language');
        Route::post('/options/language', [LanguageOptionsController::class, 'update'])->middleware('permission:options,edit')->name('options.language.update');
        Route::get('/ads', [AdsTxtController::class, 'edit'])->middleware('permission:options,view')->name('ads');
        Route::post('/ads', [AdsTxtController::class, 'update'])->middleware('permission:options,edit')->name('ads.update');
        Route::get('/ads.txt', function () {
            $content = Settings::get('ads_txt', '', 'global');
            return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
        });

        //* USERS
        Route::get('/users', [AdminUsersController::class, 'index'])->middleware('permission:users,view')->name('users');
        Route::post('/users/save', [AdminUsersController::class, 'save'])->middleware('permission:users,edit')->name('users.save');
        Route::post('/users/bulk-banned', [AdminUsersController::class, 'bulkBan'])->middleware('permission:users,edit')->name('users.bulkBan');
        Route::post('/users/bulk-action', [AdminUsersController::class, 'bulkAction'])->middleware('permission:users,edit')->name('users.bulkAction');
        Route::post('/users/bulk-unban', [AdminUsersController::class, 'bulkUnban'])->middleware('permission:users,edit')->name('users.bulkUnban');
        Route::post('/users/bulk-delete', [AdminUsersController::class, 'bulkDelete'])->middleware('permission:users,delete')->name('users.bulkDelete');

        //* PETITIONS
        Route::get('/petitions', [AdminPetitionsController::class, 'index'])->middleware('permission:petitions,view')->name('petitions');
        Route::post('/petitions/save', [AdminPetitionsController::class, 'save'])->middleware('permission:petitions,edit')->name('petitions.save');
        Route::post('/petitions/bulk-action', [AdminPetitionsController::class, 'bulkAction'])->middleware('permission:petitions,edit')->name('petitions.bulkAction');

        //* CATEGORIES
        Route::get('/categories', [AdminCategoriesController::class, 'index'])->middleware('permission:categories,view')->name('categories');
        Route::post('/categories/save', [AdminCategoriesController::class, 'save'])->middleware('permission:categories,edit')->name('categories.save');

        //* FANPAGES
        // Route::get('/fanpages', [AdminFanpagesController::class, 'index'])->middleware('permission:pages,view')->name('fanpages');

        //* SIGNATURES
        Route::get('/signatures', [AdminSignaturesController::class, 'index'])->middleware('permission:signatures,view')->name('signatures');
        Route::post('/signatures/bulk-delete', [AdminSignaturesController::class, 'bulkDelete'])->middleware('permission:signatures,delete')->name('signatures.bulkDelete');
        Route::post('/signatures/bulk-action', [AdminSignaturesController::class, 'bulkAction'])->middleware('permission:signatures,edit')->name('signatures.bulkAction');

        //* PAGES
        Route::get('/pages', [AdminPagesController::class, 'index'])->middleware('permission:pages,view')->name('pages');
        Route::post('/pages/save', [AdminPagesController::class, 'save'])->middleware('permission:pages,edit')->name('pages.save');
        Route::post('/pages/bulk-action', [AdminPagesController::class, 'bulkAction'])->middleware('permission:pages,edit')->name('pages.bulkAction');

        //* SPAM
        Route::get('/spam', [AdminSpamController::class, 'index'])->middleware('permission:spam,view')->name('spam');
        Route::post('/spam/ban', [AdminSpamController::class, 'ban'])->middleware('permission:spam,edit')->name('spam.ban');
        Route::post('/spam/unban', [AdminSpamController::class, 'unban'])->middleware('permission:spam,edit')->name('spam.unban');
        Route::post('/spam/clear', [AdminSpamController::class, 'clear'])->middleware('permission:spam,delete')->name('spam.clear');

        //* STATS
        Route::get('/stats', [AdminStatsController::class, 'index'])->middleware('permission:stats,view')->name('stats');

        //* LOGS
        Route::get('/logs', [AdminLogsController::class, 'index'])->middleware('permission:logs,view')->name('logs');
        Route::get('/logs/export', [AdminLogsController::class, 'export'])->middleware('permission:logs,view')->name('logs.export');
        Route::post('/logs/prune', [AdminLogsController::class, 'prune'])->middleware('permission:logs,delete')->name('logs.prune');
        Route::post('/logs/bulk-delete', [AdminLogsController::class, 'bulkDelete'])->middleware('permission:logs,delete')->name('logs.bulkDelete');

        //* LANGUAGES
        Route::get('/languages', [AdminLanguagesController::class, 'index'])->middleware('permission:languages,view')->name('languages.index');
        Route::post('/languages', [AdminLanguagesController::class, 'store'])->middleware('permission:languages,edit')->name('languages.store');
        Route::put('/languages/{language}', [AdminLanguagesController::class, 'update'])->middleware('permission:languages,edit')->name('languages.update');
        Route::post('/languages/{language}/default', [AdminLanguagesController::class, 'setDefault'])->middleware('permission:languages,edit')->name('languages.default');
        Route::delete('/languages/{language}', [AdminLanguagesController::class, 'destroy'])->middleware('permission:languages,delete')->name('languages.destroy');

        //* SYSTEM
        Route::get('/system/user-info', [AdminSystemController::class, 'userInfo'])->middleware('permission:system,manage')->name('system.user_info');
        Route::post('/system/user-info', [AdminSystemController::class, 'updateUserInfo'])->middleware('permission:system,manage')->name('system.user_info.update');
        Route::get('/system/user-levels', [AdminUserLevelsController::class, 'index'])->middleware('permission:system,manage')->name('system.user_levels');
        Route::post('/system/user-levels', [AdminUserLevelsController::class, 'store'])->middleware('permission:system,manage')->name('system.user_levels.store');
        Route::post('/system/user-levels/delete', [AdminUserLevelsController::class, 'delete'])->middleware('permission:system,manage')->name('system.user_levels.delete');
        Route::get('/system/permissions', [PermissionController::class, 'index'])->middleware('permission:system,manage')->name('system.permissions');
        Route::post('/system/permissions', [PermissionController::class, 'store'])->middleware('permission:system,manage')->name('system.permissions.store');

        Route::get('/utils/import', [ImportController::class, 'index'])->middleware('permission:options,edit')->name('utils.import');
        Route::post('/utils/import', [ImportController::class, 'store'])->middleware('permission:options,edit')->name('utils.import.store');
    });
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setLocale', 'block.banned.ip', 'block.banned.user'],
], function () {
    // auth + oauth
    Route::get('/oauth/google', [GoogleAuthController::class, 'redirect'])->name('oauth.google');
    Route::get('/oauth/google/callback', [GoogleAuthController::class, 'callback'])->name('oauth.google.callback');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::get('/verify/{token}', [AuthController::class, 'verify'])->name('verify.account');

    // Route::get('/user/{slug}/{id}', [UserProfileController::class, 'show'])->where(['id' => '[0-9]+'])->name('user.profile');

    Route::get('/user/{slug}/{id}', [UserProfileController::class, 'show'])
        ->where(['slug' => '[a-z0-9\-]+', 'id' => '[0-9]+'])
        ->name('user.profile');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:6,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/resend-verification', [AuthController::class, 'showResendVerification'])->name('verification.resend.form');
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend')->middleware('throttle:6,1');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('account.profile.update');
        Route::post('/account/delete', [AuthController::class, 'delete'])->name('account.delete');
        Route::get('/my-petitions', [PetitionController::class, 'myPetitions'])->name('account.petitions');

        Route::post('/account/unlink/google', [AuthController::class, 'unlinkGoogle'])->name('account.unlink.google');

        Route::post('/create-petition', [PetitionController::class, 'store'])->name('petition.store')->middleware('throttle:5,1');

        // petition owner operations
        Route::get('/petition/{slug}/{id}/edit', [PetitionController::class, 'edit'])->where(['id' => '[0-9]+'])->name('petition.edit');
        Route::post('/petition/{slug}/{id}/edit', [PetitionController::class, 'update'])->where(['id' => '[0-9]+'])->name('petition.update');
        Route::get('/petition/{slug}/{id}/download/txt', [PetitionController::class, 'downloadTxt'])->where(['id' => '[0-9]+'])->name('petition.download.txt');
        Route::get('/petition/{slug}/{id}/download/pdf', [PetitionController::class, 'downloadPdf'])->where(['id' => '[0-9]+'])->name('petition.download.pdf');
    });

    // static pages
    Route::get('/faqs', fn() => view('pages.faq'))->name('faqs');

    Route::get('/contacts', fn() => view('pages.contacts'))->name('contacts');
    Route::post('/contacts', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'text'  => ['required', 'string', 'max:5000'],
        ]);

        $adminEmail = config('mail.from.address');

        try {
            Mail::to($adminEmail)->send(new ContactMail($data['name'], $data['email'], $data['text']));
        } catch (\Exception $e) {
            AppLog::error('Contact form mail failed', $e->getMessage(), 'contacts');
        }

        return back()->with('success', 'Your message has been sent. We will get back to you soon.');
    })->name('contacts.submit')->middleware('throttle:6,1');

    // home + petitions
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/petitions', [PetitionController::class, 'index'])->name('petitions.index');
    Route::get('/petitions/category-{categorySlug}-{category}', [CategoryPetitionController::class, 'index'])
        ->where([
            'categorySlug' => '[a-z0-9\-]+',
            'category' => '[0-9]+',
        ])->name('petitions.byCategory');

    // petition creation
    Route::get('/create-petition', [PetitionController::class, 'create'])->name('petition.create');

    // petition actions (show / sign / thanks)
    Route::get('/petition/{slug}/{id}', [PetitionController::class, 'show'])->where(['id' => '[0-9]+'])->name('petition.show');
    Route::get('/petition/{slug}/{id}/signatures', [PetitionController::class, 'signatures'])->where(['id' => '[0-9]+'])->name('petition.signatures');
    Route::get('/petition/{slug}/{id}/sign', [PetitionController::class, 'signPage'])->where(['id' => '[0-9]+'])->name('petition.sign.page');
    Route::post('/petition/{slug}/{id}/sign', [PetitionController::class, 'sign'])->where(['id' => '[0-9]+'])->name('petition.sign')->middleware('throttle:10,1');
    Route::get('/petition/{slug}/{id}/thanksforsigning/{status?}', [PetitionController::class, 'thanks'])->where('id', '[0-9]+')->where('status', '([0-9]+|created)?')->name('petition.thanks');

    Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('page.show');
});
