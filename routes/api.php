<?php

// routes/api.php
use App\Http\Controllers\Api\AuthorController;
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
    Route::apiResource('index', BookController::class)
        ->names('api.books.index');

    Route::any('filterLibrary', [BookController::class, 'filterLibrary'])
        ->name('api.filterLibrary');

    Route::get('/book/{id}', [BookController::class, 'bookShow'])->name('api.books.show');

    Route::post('updateRating', [BookController::class, 'updateRating'])
        ->name('api.updateRating');

    Route::post('/storeNote', [BookController::class, 'storeNote'])
        ->name('api.notes.store');


    Route::post('updateFavorite', [BookController::class, 'updateFavorite'])
        ->name('api.updateFavorite');

    Route::post('updateProgression', [BookController::class, 'updateProgression'])
        ->name('api.updateProgression');

    Route::post('updateIndexPreference', [UserController::class, 'updateIndexPreference'])
        ->name('api.updateIndexPreference');

    Route::post('updateLanguage', [UserController::class, 'updateLanguage'])
        ->name('api.updateLanguage');

    Route::get('userProfile', [UserController::class, 'userProfile'])
        ->name('api.userProfile');

    Route::post('updateUserData', [UserController::class, 'UpdateUserData'])
        ->name('api.updateUserData');

    Route::post('updatePassword', [UserController::class, 'updatePassword'])
        ->name('api.updatePassword');

    Route::get('addGoogleBook/{id}', [BookController::class, 'googleBookStore'])
        ->name('api.addGoogleBook');

    Route::get('library', [BookController::class, 'library'])
        ->name('api.library');

    Route::get('/getGenres', [BookController::class, 'handleGenre'])
        ->name('api.getGenres');

    Route::get('/getAuthors', [BookController::class, 'handleAuthors'])
        ->name('api.getAuthors');

    Route::post('/storeBook', [BookController::class, 'storeBook'])
        ->name('api.storeBook');

    Route::post('/storeAuthor', [BookController::class, 'storeAuthor'])
        ->name('api.storeAuthor');

    Route::post('/updateBook/{id}', [BookController::class, 'updateBook'])
        ->name('api.updateBook');

    Route::get('/getBookFromAuthor/{id}', [AuthorController::class, 'bookFromAuthor'])
        ->name('api.getBookFromAuthor');
});

