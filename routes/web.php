<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookController;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', [BookController::class, 'index'])->name('book.index');

Route::middleware([CheckAdminRole::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});


Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'loginPost'])->name('loginPost');
