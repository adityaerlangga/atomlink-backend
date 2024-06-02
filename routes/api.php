<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\VariableController;
use App\Http\Controllers\Api\WorkshopController;

Route::post('requestOtp', [OwnerController::class, 'requestOtp']);
Route::post('validateOtp', [OwnerController::class, 'validateOtp']);
Route::post('owner/register', [OwnerController::class, 'register']);
Route::post('owner/login', [OwnerController::class, 'login']);

Route::prefix('variable')->group(function() {
    Route::get('cities', [VariableController::class, 'cities']);
    Route::get('units', [VariableController::class, 'units']);
});

Route::group(['middleware' => 'auth:owners'], function() {
    Route::prefix('outlets')->group(function() {
        Route::post('create', [OutletController::class, 'create']);
        Route::get('get_owner_outlets/{owner_code}', [OutletController::class, 'getOwnerOutlets']);
    });

    Route::prefix('workshops')->group(function() {
        Route::post('create', [WorkshopController::class, 'create']);
        Route::get('get_owner_workshops/{owner_code}', [WorkshopController::class, 'getOwnerWorkshops']);
    });

    Route::prefix('topups')->group(function() {
        Route::post('create', [TopupController::class, 'create']);
        Route::post('success', [TopupController::class, 'success']);

        // CRONJOB AUTO CANCEL EXPIRED TOPUP //
        Route::get('auto_cancel', [TopupController::class, 'autoCancel']);
    });
});