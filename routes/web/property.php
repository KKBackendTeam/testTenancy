<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\CSVDataController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('properties', [PropertyController::class, 'getProperties']);
    Route::get('property_edit_info/{id}', [PropertyController::class, 'propertyInfoById']);
    Route::post('add_property', [PropertyController::class, 'postAddProperty']);
    Route::delete('delete_property/{id}', [PropertyController::class, 'postDeleteProperty']);
    Route::post('property_first_step', [PropertyController::class, 'propertyUniqueRefernceChecker']);
    Route::post('property/status_update/{id}', [PropertyController::class, 'postPropertyStatusUpdate']);
    Route::post('edit_property', [PropertyController::class, 'postEditProperty']);
    Route::post('property/get_data', [CSVDataController::class, 'getPropertyCsvData']);
    Route::post('property/generate_pdf', [CSVDataController::class, 'propertyCsvDataEmail']);
});

