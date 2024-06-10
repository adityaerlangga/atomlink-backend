<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Parfume;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\VariableUnits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\VariableServiceCategory;

class ServiceController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index(Request $request)
    {
        $rules = [
            'outlet_code' => 'nullable|max:255',
            'service_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:service_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'services.service_code',
            'services.outlet_code',
            'services.service_name',
            'services.service_price',
            'services.unit_code',
            'services.service_duration_days',
            'services.service_duration_hours',
            'services.service_category_code',
            'services.created_at',
            'services.updated_at',

            'variable_service_categories.service_category_name',
            'services.is_minimum_order_quantity_active',
            'services.minimum_order_quantity_regular',
            'services.minimum_order_quantity_deposit',
            'services.is_employees_bonus_fee_active',
            'services.bonus_fee_labeling',
            'services.bonus_fee_sorting',
            'services.bonus_fee_cleaning',
            'services.bonus_fee_spotting',
            'services.bonus_fee_detailing',
            'services.bonus_fee_washing',
            'services.bonus_fee_drying',
            'services.bonus_fee_ironing',
            'services.bonus_fee_extra_ironing',
            'services.bonus_fee_folding',
            'services.bonus_fee_packaging',
            'services.is_deleted',
        ];

        $query = Service::query();

        if ($request->has('service_code')) {
            $query->where('services.service_code', $request->service_code);

            $data = $query->where('services.is_deleted', 0)
                ->leftJoin('variable_service_categories', 'services.service_category_code', '=', 'variable_service_categories.service_category_code')
                ->select($selects)
                ->first();

            if (!$data) {
                return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
            }

            return $this->sendResponse(0, "Layanan Regular berhasil ditemukan", $data);
        }

        if ($request->has('outlet_code')) {
            $query->where('services.outlet_code', $request->outlet_code);
        }


        // Apply sorting only by service_name
        if ($request->has('sort_by') && $request->sort_by == 'service_name') {
            $order_by = $request->order_by ?? 'ASC';
            $query->orderBy('services.service_name', $order_by);
        }


        // Apply sorting only by created_at
        if ($request->has('sort_by') && $request->sort_by == 'created_at') {
            $order_by = $request->order_by ?? 'ASC';
            $query->orderBy('services.created_at', $order_by);
        }

        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $data = $query->where('services.is_deleted', 0)
            ->leftJoin('variable_service_categories', 'services.service_category_code', '=', 'variable_service_categories.service_category_code')
            ->select($selects)
            ->limit($limit)
            ->offset($offset)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Layanan Regular berhasil ditemukan", $data);
    }

    public function show($service_code)
    {
        $selects = [
            'services.service_code',
            'services.outlet_code',
            'services.service_name',
            'services.service_price',
            'services.unit_code',
            'services.service_duration_days',
            'services.service_duration_hours',
            'services.service_category_code',
            'variable_service_categories.service_category_name',
            'services.is_minimum_order_quantity_active',
            'services.minimum_order_quantity_regular',
            'services.minimum_order_quantity_deposit',
            'services.is_employees_bonus_fee_active',
            'services.bonus_fee_labeling',
            'services.bonus_fee_sorting',
            'services.bonus_fee_cleaning',
            'services.bonus_fee_spotting',
            'services.bonus_fee_detailing',
            'services.bonus_fee_washing',
            'services.bonus_fee_drying',
            'services.bonus_fee_ironing',
            'services.bonus_fee_extra_ironing',
            'services.bonus_fee_folding',
            'services.bonus_fee_packaging',
            'services.is_deleted',
        ];

        $data = Service::leftJoin('variable_service_categories', 'services.service_category_code', '=', 'variable_service_categories.service_category_code')
            ->where('services.service_code', $service_code)
            ->where('services.is_deleted', 0)
            ->select($selects)
            ->first();

        if (!$data) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Layanan Regular berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|string|max:255',
            'unit_code' => 'required|string|max:255',
            'service_duration_days' => 'required|string|max:255',
            'service_duration_hours' => 'required|string|max:255',
            'service_category_code' => 'nullable|string|max:255',
            'is_minimum_order_quantity_active' => 'required|boolean',
            'minimum_order_quantity_regular' => 'required_if:is_minimum_order_quantity_active,1|numeric|max:255',
            'minimum_order_quantity_deposit' => 'required_if:is_minimum_order_quantity_active,1|numeric|max:255',
            'is_employees_bonus_fee_active' => 'required|boolean',
            'bonus_fee_labeling' => 'nullable|numeric|max:255',
            'bonus_fee_sorting' => 'nullable|numeric|max:255',
            'bonus_fee_cleaning' => 'nullable|numeric|max:255',
            'bonus_fee_spotting' => 'nullable|numeric|max:255',
            'bonus_fee_detailing' => 'nullable|numeric|max:255',
            'bonus_fee_washing' => 'nullable|numeric|max:255',
            'bonus_fee_drying' => 'nullable|numeric|max:255',
            'bonus_fee_ironing' => 'nullable|numeric|max:255',
            'bonus_fee_extra_ironing' => 'nullable|numeric|max:255',
            'bonus_fee_folding' => 'nullable|numeric|max:255',
            'bonus_fee_packaging' => 'nullable|numeric|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        DB::beginTransaction();
        try {
            $service = Service::create([
                'service_code' => generateFiledCode('SERVICE'),
                'outlet_code' => $request->outlet_code,
                'service_name' => $request->service_name,
                'service_price' => $request->service_price,
                'unit_code' => $request->unit_code,
                'service_duration_days' => $request->service_duration_days,
                'service_duration_hours' => $request->service_duration_hours,
                'service_category_code' => $request->service_category_code,
                'is_minimum_order_quantity' => $request->is_minimum_order_quantity_active,
                'minimum_order_quantity_regular' => $request->minimum_order_quantity_regular,
                'minimum_order_quantity_deposit' => $request->minimum_order_quantity_deposit,
                'is_employees_bonus_fee_active' => $request->is_employees_bonus_fee_active,
                'bonus_fee_labeling' => $request->bonus_fee_labeling,
                'bonus_fee_sorting' => $request->bonus_fee_sorting,
                'bonus_fee_cleaning' => $request->bonus_fee_cleaning,
                'bonus_fee_spotting' => $request->bonus_fee_spotting,
                'bonus_fee_detailing' => $request->bonus_fee_detailing,
                'bonus_fee_washing' => $request->bonus_fee_washing,
                'bonus_fee_drying' => $request->bonus_fee_drying,
                'bonus_fee_ironing' => $request->bonus_fee_ironing,
                'bonus_fee_extra_ironing' => $request->bonus_fee_extra_ironing,
                'bonus_fee_folding' => $request->bonus_fee_folding,
                'bonus_fee_packaging' => $request->bonus_fee_packaging,
            ]);

            if (!$service) {
                return $this->sendError(1, "Gagal menambahkan layanan regular", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Layanan Regular berhasil ditambahkan", $service);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Layanan Regular gagal ditambahkan", $e->getMessage());
        }
    }

    public function update(Request $request, $service_code)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|string|max:255',
            'unit_code' => 'required|string|max:255',
            'service_duration_days' => 'required|string|max:255',
            'service_duration_hours' => 'required|string|max:255',
            'service_category_code' => 'nullable|string|max:255',
            'is_minimum_order_quantity_active' => 'required|boolean',
            'minimum_order_quantity_regular' => 'required_if:is_minimum_order_quantity_active,1|numeric|max:255',
            'minimum_order_quantity_deposit' => 'required_if:is_minimum_order_quantity_active,1|numeric|max:255',
            'is_employees_bonus_fee_active' => 'required|boolean',
            'bonus_fee_labeling' => 'nullable|numeric|max:255',
            'bonus_fee_sorting' => 'nullable|numeric|max:255',
            'bonus_fee_cleaning' => 'nullable|numeric|max:255',
            'bonus_fee_spotting' => 'nullable|numeric|max:255',
            'bonus_fee_detailing' => 'nullable|numeric|max:255',
            'bonus_fee_washing' => 'nullable|numeric|max:255',
            'bonus_fee_drying' => 'nullable|numeric|max:255',
            'bonus_fee_ironing' => 'nullable|numeric|max:255',
            'bonus_fee_extra_ironing' => 'nullable|numeric|max:255',
            'bonus_fee_folding' => 'nullable|numeric|max:255',
            'bonus_fee_packaging' => 'nullable|numeric|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        // validate unit code and service category code
        $check_units = VariableUnits::where('unit_code', $request->unit_code)
            ->first();
        if (!$check_units) {
            return $this->sendError(1, "Kode Unit tidak ditemukan", null);
        }

        $check_service_categories = VariableServiceCategory::where('service_category_code', $request->service_category_code)
            ->first();

        if (!$check_service_categories) {
            return $this->sendError(1, "Kode Kategori Layanan Regular tidak ditemukan", null);
        }

        $service = Service::where('service_code', $service_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $service->outlet_code = $request->outlet_code;
            $service->service_name = $request->service_name;
            $service->service_price = $request->service_price;
            $service->unit_code = $request->unit_code;
            $service->service_duration_days = $request->service_duration_days;
            $service->service_duration_hours = $request->service_duration_hours;
            $service->service_category_code = $request->service_category_code;
            $service->is_minimum_order_quantity = $request->is_minimum_order_quantity_active;
            $service->minimum_order_quantity_regular = $request->minimum_order_quantity_regular;
            $service->minimum_order_quantity_deposit = $request->minimum_order_quantity_deposit;
            $service->is_employees_bonus_fee_active = $request->is_employees_bonus_fee_active;
            $service->bonus_fee_labeling = $request->bonus_fee_labeling;
            $service->bonus_fee_sorting = $request->bonus_fee_sorting;
            $service->bonus_fee_cleaning = $request->bonus_fee_cleaning;
            $service->bonus_fee_spotting = $request->bonus_fee_spotting;
            $service->bonus_fee_detailing = $request->bonus_fee_detailing;
            $service->bonus_fee_washing = $request->bonus_fee_washing;
            $service->bonus_fee_drying = $request->bonus_fee_drying;
            $service->bonus_fee_ironing = $request->bonus_fee_ironing;
            $service->bonus_fee_extra_ironing = $request->bonus_fee_extra_ironing;
            $service->bonus_fee_folding = $request->bonus_fee_folding;
            $service->bonus_fee_packaging = $request->bonus_fee_packaging;
            $service->update();

            if (!$service) {
                return $this->sendError(1, "Layanan Regular gagal diubah", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Layanan Regular berhasil diubah", $service);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Layanan Regular gagal diubah", $e->getMessage());
        }
    }

    public function destroy($service_code)
    {
        $service = Service::where('service_code', $service_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$service) {
            return $this->sendError(1, "Layanan Regular tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $service->is_deleted = 1;
            $service->update();

            if (!$service) {
                return $this->sendError(1, "Layanan Regular gagal dihapus", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Layanan Regular berhasil dihapus", $service);
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Layanan Regular gagal dihapus", $e->getMessage());
        }
    }
}
