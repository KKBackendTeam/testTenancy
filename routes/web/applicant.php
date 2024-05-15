<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApplicantBasicController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Route;

Route::post('applicant/initial_login', [ApplicantController::class, 'postInitialLoginCheck']);
Route::post('applicant/privacy', [ApplicantController::class, 'postApplicantPrivacy']);
Route::post('applicant/privacy/steps', [ApplicantController::class, 'postApplicantStepsInformation']);
Route::post('applicant/privacy/saved_information', [ApplicantController::class, 'postApplicantSavedInformation']);
Route::get('applicant/initial/get_certificate/{type}/{fileName}', [ApplicantBasicController::class, 'getCertificate']);
Route::post('applicant/applicant_info/pdf', [ApplicantController::class, 'getApplicantInfoPdf']);


Route::group(['prefix' => 'app', 'middleware' => ['applicant.config']], function () {

    Route::post('login', [UserController::class, 'appLogin']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('verify/otp', [UserController::class, 'appVerifyOTP']);

    Route::post('forgot_password', [ApplicantBasicController::class, 'forgotPassword']);
    Route::get('forgot_password/{token}', [ApplicantBasicController::class, 'forgotPasswordForm']);
    Route::post('reset_password', [ApplicantBasicController::class, 'resetYourPassword']);
});


Route::group(['prefix' => 'applicant', 'middleware' => ['applicant.config', 'jwt', 'mail_server']], function () {

    Route::get('dashboard', [ApplicantBasicController::class, 'applicantDashboard']);
    Route::post('update_profile', [ApplicantBasicController::class, 'postUpdateProfile']);
    Route::post('update_personal_info', [ApplicantBasicController::class, 'postUpdatePersonalInfo']);
    Route::post('change_password', [ApplicantBasicController::class, 'postChangePassword']);
    Route::get('download_document/{id}', [ApplicantBasicController::class, 'downloadTheDefaultDocument']);
    Route::get('get_certificate/{type}/{fileName}', [ApplicantBasicController::class, 'getCertificate']);
    Route::get('notification/{id}/mark_read', [CommonController::class, 'makeReadNotification']);
    Route::get('notification/mark_all_read', [CommonController::class, 'markAllRead']);
});



