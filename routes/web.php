<?php

use Illuminate\Support\Facades\Route;

// Serve the Vue SPA
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
