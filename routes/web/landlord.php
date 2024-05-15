<?php

use App\Http\Controllers\LandlordController;
use App\Http\Controllers\CSVDataController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('landlords', [LandlordController::class, 'getAllLandlords']);
    Route::post('landlord/get_data', [CSVDataController::class, 'getLandlordCsvData']);
    Route::get('landlord_info', [LandlordController::class, 'getLandlordsInfo']);
    Route::get('view_landlord/{id}', [LandlordController::class, 'viewLandlordById']);
    Route::post('create_landlord', [LandlordController::class, 'postCreateLandlord']);
    Route::post('edit_landlord', [LandlordController::class, 'postCreateLandlord']);
    Route::delete('landlord_delete/{id}', [LandlordController::class, 'deleteLandlordById']);
    Route::post('landlord/generate_pdf', [CSVDataController::class, 'landlordCsvDataEmail']);
});
