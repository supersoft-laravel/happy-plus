<?php

use App\Http\Controllers\API\Auth\FacebookController;
use App\Http\Controllers\API\Auth\ForgetPasswordController;
use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Frontend\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [LogoutController::class, 'logout']);

    //Resent OTP API
    Route::get('/resend-otp', [LoginController::class, 'resend_otp']);
    Route::post('/otp-verification', [LoginController::class, 'verify_otp']);

    //Notifications API
    Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);

    Route::post('/reset-password', [ForgetPasswordController::class, 'resetPassword']);
});

// Authentication Routes (Login and Register) for guests
Route::post('/login', [LoginController::class, 'login_attempt']);
Route::post('/register', [RegisterController::class, 'register_attempt']);
Route::post('/forget-password', [ForgetPasswordController::class, 'forgetPassEmail']);

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('auth/google/register', [GoogleController::class, 'GoogleLoginRegister']);

Route::get('/auth/facebook/redirect', [FacebookController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);

