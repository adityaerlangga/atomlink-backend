<?php

namespace App\Http\Controllers\Api;

use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\Owner;
use App\Models\WorkshopProductionStep;

class WorkshopController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index(Request $request)
    {
        $rules = [
            'owner_code' => 'nullable|max:255',
            'workshop_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:workshop_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'workshops.workshop_code',
            'workshops.owner_code',
            'workshops.workshop_name',
            'workshops.workshop_phone_number',
            'variable_cities.city_name as city_name',
            'workshops.workshop_address',
            'workshops.created_at',
            'workshops.updated_at',
            'workshop_production_steps.workshop_labeling',
            'workshop_production_steps.workshop_sorting',
            'workshop_production_steps.workshop_cleaning',
            'workshop_production_steps.workshop_spotting',
            'workshop_production_steps.workshop_detailing',
            'workshop_production_steps.workshop_washing',
            'workshop_production_steps.workshop_drying',
            'workshop_production_steps.workshop_ironing',
            'workshop_production_steps.workshop_extra_ironing',
            'workshop_production_steps.workshop_folding',
            'workshop_production_steps.workshop_packaging',
        ];

        $query = Workshop::query()->where('workshops.is_deleted', 0);

        if ($request->has('workshop_code')) {
            $query->where('workshops.workshop_code', $request->workshop_code);
            $data = $query->leftJoin('variable_cities', 'workshops.city_code', '=', 'variable_cities.city_code')
                ->leftJoin('workshop_production_steps', 'workshops.workshop_code', '=', 'workshop_production_steps.workshop_code')
                ->select($selects)
                ->first();

            if (!$data) {
                return $this->sendError(1, "Workshop tidak ditemukan", null);
            }

            return $this->sendResponse(0, "Workshop berhasil ditemukan", $data);
        }

        if ($request->has('owner_code')) {
            $query->where('workshops.owner_code', $request->owner_code);
        }

        if ($request->has('search')) {
            $query->where('workshops.workshop_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by')) {
            $order_by = $request->order_by ?? 'ASC';
            $query->orderBy('workshops.' . $request->sort_by, $order_by);
        }

        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $data = $query->leftJoin('variable_cities', 'workshops.city_code', '=', 'variable_cities.city_code')
            ->leftJoin('workshop_production_steps', 'workshops.workshop_code', '=', 'workshop_production_steps.workshop_code')
            ->select($selects)
            ->limit($limit)
            ->offset($offset)
            ->get();

        if ($data->isEmpty()) {
            return $this->sendError(1, "Workshop tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Workshop berhasil ditemukan", $data);
    }


    public function store(Request $request)
    {
        $rules = [
            'owner_code' => 'required|max:255',
            'workshop_name' => 'required|max:255',
            'workshop_phone_number' => 'required|max:15',
            'city_code' => 'required|max:255',
            'workshop_address' => 'required|max:255',

            // Workshop Production Step
            'workshop_labeling' => 'required|boolean',
            'workshop_sorting' => 'required|boolean',
            'workshop_cleaning' => 'required|boolean',
            'workshop_spotting' => 'required|boolean',
            'workshop_detailing' => 'required|boolean',
            'workshop_washing' => 'required|boolean',
            'workshop_drying' => 'required|boolean',
            'workshop_ironing' => 'required|boolean',
            'workshop_extra_ironing' => 'required|boolean',
            'workshop_folding' => 'required|boolean',
            'workshop_packaging' => 'required|boolean',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        // VALIDATE OWNER_CODE
        $owner = Owner::where('owner_code', $request->owner_code)->first();
        if (!$owner) {
            return $this->sendError(1, "Data Owner tidak ditemukan", null);
        }

        $workshop_code = generateFiledCode('WORKSHOP');
        DB::beginTransaction();
        try {
            $workshop = Workshop::create([
                'workshop_code' => $workshop_code,
                'owner_code' => $request->owner_code,
                'workshop_name' => $request->workshop_name,
                'workshop_phone_number' => $request->workshop_phone_number,
                'city_code' => $request->city_code,
                'workshop_address' => $request->workshop_address ?? null,
            ]);

            // Workshop Production Step
            WorkshopProductionStep::create([
                'workshop_production_step_code' => generateFiledCode('WORKSHOP_PS'),
                'workshop_code' => $workshop_code,
                'workshop_labeling' => $request->workshop_labeling,
                'workshop_sorting' => $request->workshop_sorting,
                'workshop_cleaning' => $request->workshop_cleaning,
                'workshop_spotting' => $request->workshop_spotting,
                'workshop_detailing' => $request->workshop_detailing,
                'workshop_washing' => $request->workshop_washing,
                'workshop_drying' => $request->workshop_drying,
                'workshop_ironing' => $request->workshop_ironing,
                'workshop_extra_ironing' => $request->workshop_extra_ironing,
                'workshop_folding' => $request->workshop_folding,
                'workshop_packaging' => $request->workshop_packaging,
            ]);

            $data = $workshop;
            $data->workshop_production_steps = WorkshopProductionStep::where('workshop_code', $workshop_code)->first();

            DB::commit();
            return $this->sendResponse(0, "Workshop berhasil ditambahkan", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Workshop gagal ditambahkan", $e->getMessage());
        }
    }

    public function update(Request $request, $workshop_code)
    {
        $rules = [
            'workshop_name' => 'required|max:255',
            'workshop_phone_number' => 'required|max:15',
            'city_code' => 'required|max:255',
            'workshop_address' => 'required|max:255',

            // Workshop Production Step
            'workshop_labeling' => 'required|boolean',
            'workshop_sorting' => 'required|boolean',
            'workshop_cleaning' => 'required|boolean',
            'workshop_spotting' => 'required|boolean',
            'workshop_detailing' => 'required|boolean',
            'workshop_washing' => 'required|boolean',
            'workshop_drying' => 'required|boolean',
            'workshop_ironing' => 'required|boolean',
            'workshop_extra_ironing' => 'required|boolean',
            'workshop_folding' => 'required|boolean',
            'workshop_packaging' => 'required|boolean',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $workshop = Workshop::where('workshop_code', $workshop_code)
            ->first();
        if (!$workshop) {
            return $this->sendError(1, "Workshop tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $workshop->update([
                'workshop_name' => $request->workshop_name,
                'workshop_phone_number' => $request->workshop_phone_number,
                'city_code' => $request->city_code,
                'workshop_address' => $request->workshop_address ?? null,
            ]);

            // Workshop Production Step
            $workshopProductionStep = WorkshopProductionStep::where('workshop_code', $workshop_code)->first();
            $workshopProductionStep->update([
                'workshop_labeling' => $request->workshop_labeling,
                'workshop_sorting' => $request->workshop_sorting,
                'workshop_cleaning' => $request->workshop_cleaning,
                'workshop_spotting' => $request->workshop_spotting,
                'workshop_detailing' => $request->workshop_detailing,
                'workshop_washing' => $request->workshop_washing,
                'workshop_drying' => $request->workshop_drying,
                'workshop_ironing' => $request->workshop_ironing,
                'workshop_extra_ironing' => $request->workshop_extra_ironing,
                'workshop_folding' => $request->workshop_folding,
                'workshop_packaging' => $request->workshop_packaging,
            ]);

            $data = $workshop;
            $data->workshop_production_steps = $workshopProductionStep;

            DB::commit();
            return $this->sendResponse(0, "Workshop berhasil diperbarui", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Workshop gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($workshop_code)
    {
        $workshop = Workshop::where('workshop_code', $workshop_code)->first();
        if (!$workshop) {
            return $this->sendError(1, "Data Workshop tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $workshop->update([
                'is_deleted' => 1,
            ]);

            DB::commit();
            return $this->sendResponse(0, "Workshop berhasil dihapus", []);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Workshop gagal dihapus", $e->getMessage());
        }
    }
}
