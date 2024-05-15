<?php

use App\Http\Controllers\ReferenceController;
use Illuminate\Support\Facades\Route;

Route::get('guarantor/{first}/{second}', [ReferenceController::class, 'getGuarantorForm']); // guarantor form yes/no
Route::post('guarantor/guarantor_info', [ReferenceController::class, 'postGuarantorStoreInfo']); // guarantor fills their data via post request
Route::get('employment/{first}/{second}', [ReferenceController::class, 'getEmploymentForm']); // employment form yes/no
Route::post('employment/employment_info', [ReferenceController::class, 'postEmploymentStoreInfo']); // employment fills their data via post request
Route::get('landlord/{first}/{second}', [ReferenceController::class, 'getLandlordForm']); // landlord form yes/no
Route::post('landlord/landlord_info', [ReferenceController::class, 'postLandlordStoreInfo']);


