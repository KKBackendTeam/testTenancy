<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\PostCodeGetController;
use Illuminate\Support\Facades\Route;

Route::post('register', [UserController::class, 'createNewAgency']);
Route::group(['middleware' => ['cors']], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('verify/otp', [UserController::class, 'verifyOTP']);
});

Route::post('logout', [UserController::class, 'logout']);

Route::post('forgot_password', [UserController::class, 'forgotPassword']);
Route::get('forgot_password/{token}', [UserController::class, 'forgotPasswordForm']);
Route::post('reset_password', [UserController::class, 'resetYourPassword']);
Route::get('authorize_agency/{token}', [AgencyController::class, 'authorizeAgency']);

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::post('staff/default_password', [UserController::class, 'defaultPasswordChange']);
});

Route::get('fetch_postcode/{postcode}', [PostCodeGetController::class, 'postCodeFetchFromAPI']);

