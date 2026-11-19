<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ModelShowroomController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.models.index'))->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::resource('models', ModelShowroomController::class)
            ->except('show')
            ->parameters(['models' => 'showroom']);
    });
});
