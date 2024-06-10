<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Parfume;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\VariableUnits;
use App\Models\ServiceDeposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\VariableServiceCategory;

class ServiceDepositController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index()
    {
        $selects = [
            'service_deposits.service_deposit_code',
            'service_deposits.service_code',
            'service_deposits.service_deposit_name',
            'service_deposits.service_deposit_quota',
            'service_deposits.service_deposit_discount_percentage',
            'service_deposits.service_deposit_price',
            'service_deposits.service_deposit_period_type',
            'service_deposits.service_deposit_active_period_days',
            'service_deposits.service_deposit_active_period_type',
            'service_deposits.service_deposit_expired_action',
            'service_deposits.is_active',
            'service_deposits.is_deleted',
        ];

        $service_deposits = ServiceDeposit::where('is_deleted', 0)
                    ->select($selects)
                    ->get();

        if (!$service_deposits) {
            return $this->sendError(1, "Deposit Layanan tidak ditemukan", null);
        }

        if ($service_deposits->isEmpty()) {
            return $this->sendError(1, "Deposit Layanan belum terdaftar pada sistem", null);
        }

        foreach ($service_deposits as $service_deposit) {
            $service = Service::where('service_code', $service_deposit->service_code)
                ->where('is_deleted', 0)
                ->first();

            $service_deposit->service = $service;
        }

        return $this->sendResponse(0, "Deposit Layanan berhasil ditemukan", $service_deposits);
    }

    public function show($service_deposit_code)
    {
        $selects = [
            'service_deposits.service_deposit_code',
            'service_deposits.service_code',
            'service_deposits.service_deposit_name',
            'service_deposits.service_deposit_quota',
            'service_deposits.service_deposit_discount_percentage',
            'service_deposits.service_deposit_price',
            'service_deposits.service_deposit_period_type',
            'service_deposits.service_deposit_active_period_days',
            'service_deposits.service_deposit_active_period_type',
            'service_deposits.service_deposit_expired_action',
            'service_deposits.is_active',
            'service_deposits.is_deleted',
        ];

        $service_deposit = ServiceDeposit::where('service_deposit_code', $service_deposit_code)
                    ->where('is_deleted', 0)
                    ->select($selects)
                    ->first();

        if (!$service_deposit) {
            return $this->sendError(1, "Deposit Layanan tidak ditemukan", null);
        }

        $service = Service::where('service_code', $service_deposit->service_code)
            ->first();

        if (!$service) {
            return $this->sendError(1, "Layanan Regular tidak terdaftar pada sistem", null);
        }

        $service_deposit->service = $service;

        return $this->sendResponse(0, "Deposit Layanan berhasil ditemukan", $service_deposit);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'service_code' => 'required|string|max:255',
            'service_deposit_name' => 'required|string|max:255',
            'service_deposit_quota' => 'required|numeric',
            'service_deposit_discount_percentage' => 'required|numeric|max:100',
            'service_deposit_price' => 'required|numeric',
            'service_deposit_period_type' => 'required|string|in:UNLIMITED,ACTIVE_PERIOD',
            'service_deposit_active_period_days' => 'required_if:service_deposit_period_type,1|numeric',
            'service_deposit_active_period_type' => 'required_if:service_deposit_period_type,1|string|in:ACCUMULATION,OLDEST,NEWEST',
            'service_deposit_expired_action' => 'required_if:service_deposit_period_type,1|string|in:BURN,ROLL_UP',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $outlet = Outlet::where('outlet_code', $request->outlet_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$outlet) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        $service = Service::where('service_code', $request->service_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $service_deposit_price = $request->service_deposit_price;

            $actual_deposit_price_total = $service->service_price * $request->service_deposit_quota;

            $after_discount_deposit_price_total = $actual_deposit_price_total - ($actual_deposit_price_total * ($request->service_deposit_discount_percentage / 100));

            if ($service_deposit_price != $after_discount_deposit_price_total) {
                return $this->sendError(1, "Harga Deposit Layanan tidak sesuai", [
                    'service_price_per_qty' => $service->service_price,
                    'service_deposit_qty' => $request->service_deposit_quota,

                    'your_request_service_deposit_price' => $service_deposit_price,
                    'actual_deposit_price_total' => $after_discount_deposit_price_total,
                ]);
            }

            $service_deposit = ServiceDeposit::create([
                'service_deposit_code' => generateFiledCode('SERVICE_DEPOSIT'),
                'outlet_code' => $request->outlet_code,
                'service_code' => $request->service_code,
                'service_deposit_name' => $request->service_deposit_name,
                'service_deposit_quota' => $request->service_deposit_quota,
                'service_deposit_discount_percentage' => $request->service_deposit_discount_percentage,
                'service_deposit_price' => $request->service_deposit_price,
                'service_deposit_period_type' => $request->service_deposit_period_type,
                'service_deposit_active_period_days' => $request->service_deposit_active_period_days,
                'service_deposit_active_period_type' => $request->service_deposit_active_period_type,
                'service_deposit_expired_action' => $request->service_deposit_expired_action,
            ]);

            if (!$service_deposit) {
                return $this->sendError(1, "Gagal menambahkan Deposit Layanan", null);
            }

            $service_deposit->service = $service;

            DB::commit();
            return $this->sendResponse(0, "Deposit Layanan berhasil ditambahkan", $service_deposit);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Deposit Layanan gagal ditambahkan", $e->getMessage());
        }
    }

    public function update(Request $request, $service_deposit_code)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'service_code' => 'required|string|max:255',
            'service_deposit_name' => 'required|string|max:255',
            'service_deposit_quota' => 'required|numeric',
            'service_deposit_discount_percentage' => 'required|numeric|max:100',
            'service_deposit_price' => 'required|numeric',
            'service_deposit_period_type' => 'required|string|in:UNLIMITED,ACTIVE_PERIOD',
            'service_deposit_active_period_days' => 'required_if:service_deposit_period_type,1|numeric',
            'service_deposit_active_period_type' => 'required_if:service_deposit_period_type,1|string|in:ACCUMULATION,OLDEST,NEWEST',
            'service_deposit_expired_action' => 'required_if:service_deposit_period_type,1|string|in:BURN,ROLL_UP',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $outlet = Outlet::where('outlet_code', $request->outlet_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$outlet) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        $service = Service::where('service_code', $request->service_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        $service_deposit = ServiceDeposit::where('service_deposit_code', $service_deposit_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service_deposit) {
            return $this->sendError(1, "Deposit Layanan tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $service_deposit_price = $request->service_deposit_price;

            $actual_deposit_price_total = $service->service_price * $request->service_deposit_quota;

            $after_discount_deposit_price_total = $actual_deposit_price_total - ($actual_deposit_price_total * ($request->service_deposit_discount_percentage / 100));

            if ($service_deposit_price != $after_discount_deposit_price_total) {
                return $this->sendError(1, "Harga Deposit Layanan tidak sesuai", [
                    'service_price_per_qty' => $service->service_price,
                    'service_deposit_qty' => $request->service_deposit_quota,

                    'your_request_service_deposit_price' => $service_deposit_price,
                    'actual_deposit_price_total' => $after_discount_deposit_price_total,
                ]);
            }

            $service_deposit->outlet_code = $request->outlet_code;
            $service_deposit->service_code = $request->service_code;
            $service_deposit->service_deposit_name = $request->service_deposit_name;
            $service_deposit->service_deposit_quota = $request->service_deposit_quota;
            $service_deposit->service_deposit_discount_percentage = $request->service_deposit_discount_percentage;
            $service_deposit->service_deposit_price = $request->service_deposit_price;
            $service_deposit->service_deposit_period_type = $request->service_deposit_period_type;
            $service_deposit->service_deposit_active_period_days = $request->service_deposit_active_period_days;
            $service_deposit->service_deposit_active_period_type = $request->service_deposit_active_period_type;
            $service_deposit->service_deposit_expired_action = $request->service_deposit_expired_action;
            $service_deposit->update();

            if (!$service_deposit) {
                return $this->sendError(1, "Deposit Layanan gagal diubah", null);
            }

            $service_deposit->service = $service;

            DB::commit();
            return $this->sendResponse(0, "Deposit Layanan berhasil diubah", $service_deposit);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Deposit Layanan gagal diubah", $e->getMessage());
        }
    }

    public function destroy($service_deposit_code)
    {
        $service_deposit = ServiceDeposit::where('service_deposit_code', $service_deposit_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service_deposit) {
            return $this->sendError(1, "Deposit Layanan tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $service_deposit->is_deleted = 1;
            $service_deposit->update();

            if (!$service_deposit) {
                return $this->sendError(1, "Deposit Layanan gagal dihapus", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Deposit Layanan berhasil dihapus", $service_deposit);
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Deposit Layanan gagal dihapus", $e->getMessage());
        }
    }

    public function getByOutlet($outlet_code)
    {
        $selects = [
            'service_deposits.service_deposit_code',
            'service_deposits.service_code',
            'service_deposits.service_deposit_name',
            'service_deposits.service_deposit_quota',
            'service_deposits.service_deposit_discount_percentage',
            'service_deposits.service_deposit_price',
            'service_deposits.service_deposit_period_type',
            'service_deposits.service_deposit_active_period_days',
            'service_deposits.service_deposit_active_period_type',
            'service_deposits.service_deposit_expired_action',
            'service_deposits.is_active',
            'service_deposits.is_deleted',
        ];

        $service_deposits = ServiceDeposit::where('outlet_code', $outlet_code)
                    ->where('is_deleted', 0)
                    ->select($selects)
                    ->get();

        if (!$service_deposits) {
            return $this->sendError(1, "Deposit Layanan tidak ditemukan", null);
        }

        if ($service_deposits->isEmpty()) {
            return $this->sendError(1, "Deposit Layanan belum terdaftar pada sistem", null);
        }

        foreach ($service_deposits as $service_deposit) {
            $service = Service::where('service_code', $service_deposit->service_code)
                ->where('is_deleted', 0)
                ->first();

            $service_deposit->service = $service;
        }

        return $this->sendResponse(0, "Deposit Layanan berhasil ditemukan", $service_deposits);
    }
}
