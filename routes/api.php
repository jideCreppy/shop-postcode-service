<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::post('/shops', [ShopController::class, 'store'])->name('post.store');
Route::get('/shops', [ShopController::class, 'index'])->name('get.store');
