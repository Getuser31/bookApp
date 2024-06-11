<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', [BookController::class, 'index'])->name('book.index');

Route::middleware([CheckAdminRole::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/admin/genre', [AdminController::class, 'handleGenre'])->name('admin.genre');
    Route::get('/admin/genre/{id}/edit', [AdminController::class, 'editGenre'])->name('admin.genre.edit');
    Route::put('/admin/genre/{id}', [AdminController::class, 'updateGenre'])->name('admin.genre.update');
    Route::get('/admin/genre/create', [AdminController::class, 'createGenre'])->name('admin.genre.create');
    Route::post('/admin/genre', [AdminController::class, 'storeGenre'])->name('admin.genre.store');
    Route::delete('/admin/genre/{id}', [AdminController::class, 'deleteGenre'])->name('admin.genre.delete');

    Route::get('/admin/book', [AdminController::class, 'handleBook'])->name('admin.book');

    Route::get('/admin/author', [AdminController::class, 'handleAuthor'])->name('admin.author');
    Route::get('/admin/author/create', [AdminController::class, 'createAuthor'])->name('admin.author.create');
    Route::get('/admin/author/{id}/edit', [AdminController::class, 'editAuthor'])->name('admin.author.edit');
    Route::put('/admin/author/{id}', [AdminController::class, 'updateAuthor'])->name('admin.author.update');
    Route::post('/admin/author', [AdminController::class, 'storeAuthor'])->name('admin.author.store');
    Route::delete('/admin/author/{id}', [AdminController::class, 'deleteAuthor'])->name('admin.author.delete');

    Route::get('/admin/collection', [AdminController::class, 'handleCollection'])->name('admin.collection');
});


Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'loginPost'])->name('loginPost');
route::get('/logout', [UserController::class, 'logout'])->name('logout');
