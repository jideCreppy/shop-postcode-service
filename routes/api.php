<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::controller(ShopController::class)->group(function () {
    Route::post('/shops', 'store')->name('add.store');
    Route::post('/shops/search', 'search')->name('search.store');
});
