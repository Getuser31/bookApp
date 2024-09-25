<?php

// routes/api.php
use App\Http\Controllers\Api\BookController;

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::group(['middleware' => 'api'], function () {
    // CSRF cookie endpoint
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])->name('sanctum.csrf-cookie');
    Route::post('/login', [UserController::class, 'login'])->name('api.login');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('index', BookController::class)->names('api.books.index');
    Route::any('filterLibrary', [BookController::class, 'filterLibrary'])
        ->name('api.filterLibrary');

    Route::get('/book/{id}', [BookController::class, 'bookShow'])->name('api.books.show');

    Route::post('updateRating', [BookController::class, 'updateRating'])
        ->name('api.updateRating');

    Route::post('updateFavorite', [BookController::class, 'updateFavorite'])
        ->name('api.updateFavorite');

    Route::post('updateProgression', [BookController::class, 'updateProgression'])
        ->name('api.updateProgression');

    Route::post('updateIndexPreference', [UserController::class, 'updateIndexPreference'])
        ->name('api.updateIndexPreference');

    Route::post('updateLanguage', [UserController::class, 'updateLanguage'])
        ->name('api.updateLanguage');
});

