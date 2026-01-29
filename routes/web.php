<?php

use App\Http\Controllers\AuthController;
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

    Route::get('/', function () {
        return view('pages.home', [
            'categories' => [
                'Animals',
                'Business and Companies',
                'City Life',
                'Culture and Society',
                'Education',
                'Environment',
                'Health and Wellness',
                'Human Rights',
                'International Affairs',
                'Law and Justice',
                'Media and Entertainment',
                'Politics',
                'Religion and Spirituality',
                'Science and Technology',
                'Sports',
                'Transportation',
                'Travel and Tourism',
                'Work and Employment',
                'Youth and Family',
                'Food and Agriculture',
                'Housing and Urban Development',
                'Energy and Resources',
                'Public Safety'
            ],
        ]);
    });

    Route::get('/petition/{slug}/{id}', function ($locale, $slug, $id) {
        return view('petition.demo_show', compact('locale', 'slug', 'id'));
    });

    Route::get('/petitions', function () {
        return view('pages.petitions');
    });

    Route::get('/faqs', function () {
        return view('pages.faq');
    });
});
