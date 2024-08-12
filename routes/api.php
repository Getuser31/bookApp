<?php

// routes/api.php
use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::group(['middleware' => 'api'], function() {
    // CSRF cookie endpoint
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])->name('sanctum.csrf-cookie');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('index', BookController::class)->names('api.books.index');
    Route::any('filterLibrary', [BookController::class, 'filterLibrary'])
        ->name('api.filterLibrary');

    Route::post('updateRating', [BookController::class, 'updateRating'])
        ->name('api.updateRating');
});

