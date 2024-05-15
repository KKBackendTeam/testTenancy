<?php

use App\Http\Controllers\BasicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BasicController::class, 'welcome']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/all', [BasicController::class, 'work']);

Route::group(['prefix' => 'storage', 'middleware' => ['jwt_file']], function () {

    Route::get('/agency/applicant/{directory_name?}/{filename?}', [FileController::class, 'getFileFromAgency2']);
    Route::get('/agency/{directory_name?}/{filename?}', [FileController::class, 'getFileFromAgency']);
});

Route::group(['prefix' => 'storage', 'middleware' => ['applicant.config', 'jwt_file']], function () {

    Route::get('/applicant/documents/{filename?}', [FileController::class, 'getFileFromApplicant4']);
    Route::get('/applicant/agency/{directory_name?}/{filename?}', [FileController::class, 'getFileFromApplicant3']);
    Route::get('/applicant/documents/{directory_name?}/{filename?}', [FileController::class, 'getFileFromApplicant2']);
    Route::get('/applicant/{directory_name?}/{filename?}', [FileController::class, 'getFileFromApplicant']);
});

Route::get('fetch/storage/agency/media_logo/{filename}', [FileController::class, 'getMediaLogo'])->middleware('jwt_file');
Route::get('fetch_logo/storage/agency/media_logo/{filename}', [FileController::class, 'getMediaLogoForAll']); //->middleware('jwt_file');
Route::get('fetch/storage/applicant/agreement_signature/{filename}', [FileController::class, 'getAgreementSignature'])->middleware('jwt_file');

Route::group(['middleware' => ['applicant.config', 'jwt_file']], function () {

    Route::get('fetch_applicant/storage/applicant/agreement_signature/{filename}', [FileController::class, 'getAgreementSignature']);
});
