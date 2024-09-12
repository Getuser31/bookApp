<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('book.index');

    Route::get('/library', [BookController::class, 'library'])->middleware('auth')->name('book.library');
    Route::get('/book/{id}', [BookController::class, 'show'])->name('book.show');
    Route::post('/updateProgression', [BookController::class, 'updateProgression'])->name('book.updateProgression');
    Route::post('/deleteBook/{id}', [BookController::class, 'deleteBook'])->name('book.deleteBook');
    Route::get('/addBook/{title?}', [BookController::class, 'addBook'])->name('book.addBook');
    Route::get('/searchBook', [BookController::class, 'searchBook'])->name('book.searchBook');
    Route::get('/addBook/{id}', [BookController::class, 'addBookPost'])->name('book.addBookPost');
    Route::get('/removeBook/{id}', [BookController::class, 'removeBook'])->name('book.removeBook');
    Route::post('/storeNote', [BookController::class, 'storeNote'])->name('notes.store');

//Google API
    Route::post('/googleBook/', [BookController::class, 'googleBook'])->name('book.googleBook');
    Route::get('/googleBookStore/{id}', [BookController::class, 'googleBookStore'])->name('book.googleBookStore');
});

//admin
Route::middleware([CheckAdminRole::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/admin/genre', [AdminController::class, 'handleGenre'])->name('admin.genre');
    Route::get('/admin/genre/{id}/edit', [AdminController::class, 'editGenre'])->name('admin.genre.edit');
    Route::put('/admin/genre/{id}', [AdminController::class, 'updateGenre'])->name('admin.genre.update');
    Route::get('/admin/genre/create', [AdminController::class, 'createGenre'])->name('admin.genre.create');
    Route::post('/admin/genre', [AdminController::class, 'storeGenre'])->name('admin.genre.store');
    Route::delete('/admin/genre/{id}', [AdminController::class, 'deleteGenre'])->name('admin.genre.delete');

    Route::get('/admin/book', [AdminController::class, 'handleBook'])->name('admin.book');
    Route::get('/admin/book/{id}/edit', [AdminController::class, 'editBook'])->name('admin.book.edit');
    Route::get('/admin/book/create', [AdminController::class, 'createBook'])->name('admin.book.create');
    Route::put('/admin/book/{id}/update', [AdminController::class, 'updateBook'])->name('admin.book.update');
    Route::post('/admin/book/store', [AdminController::class, 'storeBook'])->name('admin.book.store');
    Route::delete('/admin/book/{id}', [AdminController::class, 'deleteBook'])->name('admin.book.delete');

    Route::get('/admin/author', [AdminController::class, 'handleAuthor'])->name('admin.author');
    Route::get('/admin/author/create', [AdminController::class, 'createAuthor'])->name('admin.author.create');
    Route::get('/admin/author/{id}/edit', [AdminController::class, 'editAuthor'])->name('admin.author.edit');
    Route::put('/admin/author/{id}', [AdminController::class, 'updateAuthor'])->name('admin.author.update');
    Route::post('/admin/author', [AdminController::class, 'storeAuthor'])->name('admin.author.store');
    Route::delete('/admin/author/{id}', [AdminController::class, 'deleteAuthor'])->name('admin.author.delete');

    Route::get('/admin/collection', [AdminController::class, 'handleCollection'])->name('admin.collection');
    Route::get('/admin/collection/{id}/edit', [AdminController::class, 'editCollection'])->name('admin.collection.edit');
    Route::get('/admin/collection/create', [AdminController::class, 'createCollection'])->name('admin.collection.create');
    Route::post('/admin/collection', [AdminController::class, 'storeCollection'])->name('admin.collection.store');
    Route::delete('/admin/collection/{id}', [AdminController::class, 'deleteCollection'])->name('admin.collection.delete');
    Route::put('/admin/collection/{id}', [AdminController::class, 'updateCollection'])->name('admin.collection.update0');

    Route::get('/admin/users', [AdminController::class, 'handleUsers'])->name('admin.users');
    Route::post('/admin/users/seekUser', [AdminController::class, 'seekUser'])->name('admin.seekUser');
    Route::post('/admin/user/create', [UserController::class, 'store'])->name('admin.user.create');
    Route::delete('/admin/user/{id}', [UserController::class, 'deleteUser'])->name('admin.user.delete');
    Route::get('/admin/userList', [UserController::class, 'listOfUsers'])->name('admin.user.list');
});

//User
Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'loginPost'])->name('loginPost');

Route::middleware(['auth:sanctum'])->group(function () {
    route::get('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/users/{id}', [UserController::class, 'checkUser'])->name('checkUser');
    Route::get('/createAccount', [UserController::class, 'createAccount'])->name('createAccount');
    Route::post('/register', [UserController::class, 'register'])->name('register');
    Route::get('/updateAccount', [UserController::class, 'updateAccount'])->name('updateAccount');
    Route::put('/updateAccount', [UserController::class, 'updateAccountPost'])->name('updateAccountPost');
    Route::post('/updateUserData', [UserController::class, 'UpdateUserData'])->name('UpdateUserData');
    Route::get('/profile', [UserController::class, 'userProfile'])->name('userProfile');
    Route::post('/updatePassword', [UserController::class, 'updatePassword'])->name('updatePassword');
});
