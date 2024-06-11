<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\ParfumeController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\VariableController;
use App\Http\Controllers\Api\WorkshopController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\ServiceDepositController;

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

Route::get('unauthorized', [AuthController::class, 'unauthorized'])->name('unauthorized');


// CRONJOB AUTO CANCEL EXPIRED TOPUP //
Route::get('auto_cancel', [TopupController::class, 'autoCancel']);

// ======================= END: SETUP ======================= //

Route::post('requestOtp', [OwnerController::class, 'requestOtp']);
Route::post('validateOtp', [OwnerController::class, 'validateOtp']);
Route::post('owner/register', [OwnerController::class, 'register']);
Route::post('owner/login', [OwnerController::class, 'login']);

Route::group(['middleware' => 'auth:owners'], function() {
    Route::prefix('owner')->group(function() {
        Route::get('finance', [OwnerController::class, 'finance']);
        Route::get('cash-drawers', [OwnerController::class, 'cash_drawers']);
        Route::get('cash-banks', [OwnerController::class, 'cash_banks']);
        Route::get('cash-merchants', [OwnerController::class, 'cash_merchants']);
        Route::get('cashflow', [OwnerController::class, 'cashflow']);
        Route::get('gross-revenue', [OwnerController::class, 'gross_revenue']);
        Route::get('today-activities', [OwnerController::class, 'today_activities']);
        Route::get('employee-attendances', [OwnerController::class, 'employee_attendances']);
    });
    
    Route::prefix('outlets')->group(function() {
        Route::resource('/', OutletController::class)->parameters(['' => 'outlet_code']);
    });

    Route::prefix('workshops')->group(function() {
        Route::resource('/', WorkshopController::class)->parameters(['' => 'workshop_code']);
    });

    Route::prefix('customers')->group(function() {
        Route::resource('/', CustomerController::class)->parameters(['' => 'customer_code']);
        Route::get('owner/{owner_code}', [CustomerController::class, 'getByOwner']);
        Route::get('outlet/{owner_code}', [CustomerController::class, 'getByOutlet']);
    });

    Route::prefix('topups')->group(function() {
        Route::post('create', [TopupController::class, 'create']);
        Route::post('success', [TopupController::class, 'success']);
        Route::get('all', [TopupController::class, 'all']);
        Route::get('owner/{owner_code}', [TopupController::class, 'getByOwner']);
    });

    Route::prefix('parfumes')->group(function() {
        Route::resource('/', ParfumeController::class)->parameters(['' => 'parfume_code']);
        Route::get('outlet/{outlet_code}', [ParfumeController::class, 'getByOutlet']);
    });

    Route::prefix('services')->group(function() {
        Route::resource('/', ServiceController::class)->parameters(['' => 'service_code']);
    });

    Route::prefix('service-deposits')->group(function() {
        Route::resource('/', ServiceDepositController::class)->parameters(['' => 'service_deposit_code']);
        Route::get('outlet/{outlet_code}', [ServiceDepositController::class, 'getByOutlet']);
    });

    Route::prefix('racks')->group(function() {
        Route::resource('/', RackController::class)->parameters(['' => 'rack_code']);
        Route::get('outlet/{outlet_code}', [RackController::class, 'getByOutlet']);
    });

    Route::prefix('variable')->group(function() {
        Route::get('cities', [VariableController::class, 'cities']);
        Route::get('units', [VariableController::class, 'units']);
        Route::get('banks', [VariableController::class, 'banks']);
        Route::get('service_categories', [VariableController::class, 'service_categories']);
    });
});
