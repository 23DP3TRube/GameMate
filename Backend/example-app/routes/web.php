<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Gamemate API',
        'frontend' => 'http://localhost:5173',
        'docs' => '/api/home',
    ]);
})->name('home');
