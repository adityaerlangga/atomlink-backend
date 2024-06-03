<?php

use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\VariableController;
use App\Http\Controllers\Api\WorkshopController;

// ======================= START: SETUP ======================= //
Route::get('setup', function() {
    Artisan::call('migrate:fresh --seed');
    Artisan::call('optimize:clear');
    Artisan::call('storage:link');

    return response()->json([
        'message' => 'Setup completed'
    ]);
});

// Optimize and clear all cache
Route::get('optimize', function() {
    Artisan::call('optimize:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('event:clear');


    return response()->json([
        'message' => 'Optimize and clear cache completed'
    ]);
});


// CRONJOB AUTO CANCEL EXPIRED TOPUP //
Route::get('auto_cancel', [TopupController::class, 'autoCancel']);

// ======================= END: SETUP ======================= //

Route::post('requestOtp', [OwnerController::class, 'requestOtp']);
Route::post('validateOtp', [OwnerController::class, 'validateOtp']);
Route::post('owner/register', [OwnerController::class, 'register']);
Route::post('owner/login', [OwnerController::class, 'login']);



Route::prefix('variable')->group(function() {
    Route::get('cities', [VariableController::class, 'cities']);
    Route::get('units', [VariableController::class, 'units']);
    Route::get('banks', [VariableController::class, 'banks']);
});

Route::group(['middleware' => 'auth:owners'], function() {
    Route::prefix('outlets')->group(function() {
        Route::resource('/', OutletController::class)->parameters(['' => 'outlet_code']);
        Route::get('owner/{owner_code}', [OutletController::class, 'getOwnerOutlets']);
    });

    Route::prefix('workshops')->group(function() {
        Route::resource('/', WorkshopController::class)->parameters(['' => 'workshop_code']);
        Route::get('owner/{owner_code}', [WorkshopController::class, 'getOwnerWorkshops']);
    });

    Route::prefix('customers')->group(function() {
        Route::resource('/', CustomerController::class)->parameters(['' => 'customer_code']);
        Route::get('owner/{owner_code}', [CustomerController::class, 'getOwnerCustomers']);
        Route::get('outlet/{owner_code}', [CustomerController::class, 'getOutletCustomers']);
    });

    Route::prefix('topups')->group(function() {
        Route::post('create', [TopupController::class, 'create']);
        Route::post('success', [TopupController::class, 'success']);
        Route::get('all', [TopupController::class, 'all']);
        Route::get('get_owner_topups/{owner_code}', [TopupController::class, 'getOwnerTopups']);
    });
});
