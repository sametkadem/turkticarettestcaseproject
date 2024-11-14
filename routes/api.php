<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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

    Route::get('products/list', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('products/store', [ProductController::class, 'store']); // admin
    Route::delete('products/{id}', [ProductController::class, 'destroy']); // admin

    Route::get('cart', [CartController::class, 'getCart']);
    Route::get('cart/list', [CartController::class, 'getCartList']);
    Route::post('cart/item/store', [CartController::class, 'addItem']);
    Route::put('cart/item/{id}', [CartController::class, 'updateItem']);
    Route::delete('cart/item/{id}', [CartController::class, 'removeItem']);

    Route::post('order/store', [OrderController::class, 'createOrder']);
    Route::get('order/list', [OrderController::class, 'getOrders']);
    Route::get('order/{id}', [OrderController::class, 'getOrder']);
});


