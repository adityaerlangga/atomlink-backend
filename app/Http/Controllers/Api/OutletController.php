<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class OutletController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function create(Request $request)
    {
        $rules = [
            'owner_code' => 'required|max:255',
            'outlet_name' => 'required|max:255',
            'outlet_phone_number' => 'required|max:15',
            'city_code' => 'required|max:255',
            'outlet_address' => 'required|max:255',
            'outlet_logo' => 'nullable',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        // VALIDATE OWNER_CODE
        $owner = Owner::where('owner_code', $request->owner_code)->first();
        if (!$owner) {
            return $this->sendError(1, "Data Owner tidak ditemukan", null);
        }

        $outlet_code = generateFiledCode('OUTLET');
        DB::beginTransaction();
        try {
            $data = Outlet::create([
                'outlet_code' => $outlet_code,
                'owner_code' => $request->owner_code,
                'outlet_name' => $request->outlet_name,
                'outlet_phone_number' => $request->outlet_phone_number,
                'city_code' => $request->city_code,
                'outlet_address' => $request->outlet_address,
                'outlet_logo' => $request->outlet_logo ?? null,
            ]);

            DB::commit();
            return $this->sendResponse(0, "Outlet berhasil ditambahkan", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Outlet gagal ditambahkan", $e->getMessage());
        }
    }

    public function getOwnerOutlets($owner_code)
    {
        $selects = [
            'outlets.outlet_code',
            'outlets.owner_code',
            'outlets.outlet_name',
            'outlets.outlet_phone_number',
            'variable_cities.city_name as city_name',
            'outlets.outlet_address',
            'outlets.outlet_logo',
        ];

        // VALIDATE OWNER_CODE
        $owner = Owner::where('owner_code', $owner_code)->first();
        if (!$owner) {
            return $this->sendError(1, "Data Owner tidak ditemukan", null);
        }

        $data = Outlet::where('owner_code', $owner_code)
            ->leftJoin('variable_cities', 'outlets.city_code', '=', 'variable_cities.city_code')
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Owner belum memiliki Outlet yang terdaftar", null);
        }

        return $this->sendResponse(0, "Outlet berhasil ditemukan", $data);
    }


}
