<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeyValueObjectController;

// Key-Value Object endpoints
Route::prefix('object')->group(function () {
    Route::get('/get_all_records', [KeyValueObjectController::class, 'index']);
    Route::post('/', [KeyValueObjectController::class, 'store']);
    Route::get('/{key}', [KeyValueObjectController::class, 'getByKey']);
});

