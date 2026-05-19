<?php

use Illuminate\Support\Facades\Route;

Route::options('{any}', function () {
    return response('', 200, [
        'Access-Control-Allow-Origin'  => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
    ]);
})->where('any', '.*');

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\ApiTokenAuth;

Route::get('/home', fn() => response()->json(['title' => 'Gamemate', 'version' => '1.0']));
Route::get('/games', [GamesController::class, 'index']);

Route::post('/register',            [AuthController::class, 'register']);
Route::post('/login',               [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/forgot-password',     [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',      [AuthController::class, 'resetPassword']);

Route::middleware(ApiTokenAuth::class)->group(function () {
    Route::get('/me',                      [AuthController::class, 'me']);
    Route::post('/logout',                 [AuthController::class, 'logout']);
    Route::delete('/account',             [AuthController::class, 'deleteAccount']);
    Route::post('/resend-verification',    [AuthController::class, 'resendVerification']);

    Route::put('/profile',              [ProfileController::class, 'update']);
    Route::post('/profile/avatar',      [ProfileController::class, 'uploadAvatar']);
    Route::get('/profiles/{id}',        [ProfileController::class, 'show']);

    Route::get('/tracker/rank',          [TrackerController::class, 'rank']);

    Route::prefix('admin')->group(function () {
        Route::get('/users',                    [AdminController::class, 'users']);
        Route::delete('/users/{id}',            [AdminController::class, 'deleteUser']);
        Route::post('/users/{id}/toggle-admin', [AdminController::class, 'toggleAdmin']);
        Route::get('/stats',                    [AdminController::class, 'stats']);
        Route::get('/export/pdf',               [AdminController::class, 'exportPdf']);
        Route::get('/export/csv',               [AdminController::class, 'exportCsv']);
    });

    Route::get('/discover',             [MatchController::class, 'discover']);
    Route::post('/swipe',               [MatchController::class, 'swipe']);
    Route::get('/matches',              [MatchController::class, 'matches']);
    Route::get('/matches/{id}/messages',  [MatchController::class, 'messages']);
    Route::post('/matches/{id}/messages', [MatchController::class, 'sendMessage']);
});
