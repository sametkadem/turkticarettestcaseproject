<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class,'login']);
    Route::post('register', [AuthController::class,'register']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('auth/me', [AuthController::class,'me']);
    Route::post('auth/logout', [AuthController::class,'logout']);
    Route::post('auth/refresh', [AuthController::class,'refresh']);
    Route::get('categories/list', [CategoryController::class, 'index']);
    Route::get('categories/list/tree', [CategoryController::class, 'tree']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::post('categories/store', [CategoryController::class, 'store']); // admin
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']); // admin
});


