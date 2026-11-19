<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ModelShowroomController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Public (read-only)
Route::get('model-showrooms', [ModelShowroomController::class, 'index']);
Route::get('model-showrooms/{id}', [ModelShowroomController::class, 'show'])->whereNumber('id');

// Protected: needs "Authorization: Bearer <token>"
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('model-showrooms', [ModelShowroomController::class, 'store']);
    Route::match(['put', 'patch'], 'model-showrooms/{id}', [ModelShowroomController::class, 'update'])->whereNumber('id');
    Route::delete('model-showrooms/{id}', [ModelShowroomController::class, 'destroy'])->whereNumber('id');
});
