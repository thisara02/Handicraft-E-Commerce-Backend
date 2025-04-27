<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CartController;

Route::middleware(['auth'])->group(function () {
    Route::get('/cart/items', [CartController::class, 'getCartItems']);
});