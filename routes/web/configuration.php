<?php

use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DefaultDocumentsController;
use App\Http\Controllers\DefaultSettingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency/configuration', 'middleware' => ['jwt', 'mail_server']], function () {
    Route::post('buy_credit', [ConfigurationController::class, 'postBuyCredit']);
});

Route::group(['prefix' => 'agency/configuration', 'middleware' => ['jwt']], function () {
    Route::post('email_server', [ConfigurationController::class, 'postMailServer']);
    Route::get('email_server', [ConfigurationController::class, 'getMailServer']);
    Route::get('property_csv_demo', [ConfigurationController::class, 'getPropertyCsvDemo']);
    Route::get('landlord_csv_demo', [ConfigurationController::class, 'getLandlordCsvDemo']);
    Route::post('bulk_landlord', [ConfigurationController::class, 'postBulkLandlord']);
    Route::post('bulk_property', [ConfigurationController::class, 'postBulkProperty']);
    Route::post('media_logo', [ConfigurationController::class, 'postMediaLogo']);
    Route::get('chasing', [ConfigurationController::class, 'getChasingSetting']);
    Route::post('chasing', [ConfigurationController::class, 'postChasingSetting']);
    Route::get('financial', [ConfigurationController::class, 'getFinancialConfiguration']);
    Route::post('financial', [ConfigurationController::class, 'postFinancialConfiguration']);
    Route::get('credits', [ConfigurationController::class, 'getCredit']);
    Route::get('agency_information', [ConfigurationController::class, 'getAgencyInformation']);
    Route::post('edit_agency', [ConfigurationController::class, 'postUpdateAgencyProfile']);
});

Route::group(['prefix' => 'admin/configuration', 'middleware' => ['jwt', 'admin'], 'onlyFor' => 'SuperAdmin'], function () {
    Route::post('email_server', [ConfigurationController::class, 'postMailServer']);
    Route::get('email_server', [ConfigurationController::class, 'getMailServer']);
    Route::get('agency_information', [ConfigurationController::class, 'getAgencyInformation']);
    Route::post('edit_agency', [ConfigurationController::class, 'postUpdateAgencyProfile']);
});

Route::group(['prefix' => 'agency', 'middleware' => ['jwt'], 'roles' => 'Agency'], function () {
    Route::get('documents', [DefaultDocumentsController::class, 'getAllTheDocuments']);
    Route::post('add_document', [DefaultDocumentsController::class, 'postAddDefaultDocuments']);
    Route::post('update_document', [DefaultDocumentsController::class, 'postUpdateDefaultDocuments']);
    Route::delete('delete_document/{id}', [DefaultDocumentsController::class, 'deleteDefaultDocuments']);
    Route::post('reset_template', [DefaultSettingController::class, 'resetMailTemplate']);
    Route::post('reset_text', [DefaultSettingController::class, 'resetTextTemplate']);
});

