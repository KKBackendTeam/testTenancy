<?php

use App\Http\Controllers\CustomizationController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency/customization', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('mail_template', [CustomizationController::class, 'getEmailTemplate']);
    Route::post('mail_template', [CustomizationController::class, 'postEmailTemplate']);
    Route::get('text_for', [CustomizationController::class, 'getTextForSpecificArea']);
    Route::post('text_for', [CustomizationController::class, 'postTextForSpecificArea']);
});

Route::group(['prefix' => 'admin/customization', 'middleware' => ['jwt'], 'roles' => 'SuperAdmin'], function () {
    Route::get('mail_template', [CustomizationController::class, 'getEmailTemplate']);
    Route::post('mail_template', [CustomizationController::class, 'postEmailTemplate']);
    Route::get('text_for', [CustomizationController::class, 'getTextForSpecificArea']);
    Route::post('text_for', [CustomizationController::class, 'postTextForSpecificArea']);
});

