<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ModelShowroomController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- public site
Route::get('/', [HomeController::class, 'index'])->name('home');

// ---------------------------------------------------------------- admin panel
Route::prefix('admin')->name('admin.')->group(function () {

    // login (only for logged-out visitors)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    });

    // everything below needs a logged-in admin (see App\Http\Middleware\AdminMiddleware)
    Route::middleware('admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.models.index'))->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Model showroom: list
        Route::get('models', [ModelShowroomController::class, 'index'])->name('models.index');

        // Model showroom: add
        Route::get('models/create', [ModelShowroomController::class, 'create'])->name('models.create');
        Route::post('models', [ModelShowroomController::class, 'store'])->name('models.store');

        // Model showroom: edit
        Route::get('models/{showroom}/edit', [ModelShowroomController::class, 'edit'])->name('models.edit');
        Route::put('models/{showroom}', [ModelShowroomController::class, 'update'])->name('models.update');

        // Model showroom: delete
        Route::delete('models/{showroom}', [ModelShowroomController::class, 'destroy'])->name('models.destroy');
    });
});
