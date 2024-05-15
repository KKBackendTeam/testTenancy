<?php

use App\Http\Controllers\NoMiddlewareController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'agency'], function () {
    Route::post('thank_you', [NoMiddlewareController::class, 'thankYouPageInformation']);
});
