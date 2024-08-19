<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::post('/shops', [ShopController::class, 'store']);
Route::get('/shops', [ShopController::class, 'show']);
