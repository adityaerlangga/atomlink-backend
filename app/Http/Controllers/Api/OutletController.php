<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Workshop;
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

    public function index(Request $request)
    {
        $rules = [
            'owner_code' => 'nullable|max:255',
            'outlet_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:outlet_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'outlets.outlet_code',
            'outlets.owner_code',
            'outlets.outlet_name',
            'outlets.outlet_phone_number',
            'variable_cities.city_name as city_name',
            'outlets.outlet_address',
            'outlets.outlet_logo',
        ];

        $query = Outlet::query();

        if ($request->has('outlet_code')) {
            $query->where('outlet_code', $request->outlet_code);

            $data = $query->where('is_deleted', 0)
                ->leftJoin('variable_cities', 'outlets.city_code', '=', 'variable_cities.city_code')
                ->select($selects)
                ->first();

            if (!$data) {
                return $this->sendError(1, "Outlet tidak ditemukan", null);
            }

            return $this->sendResponse(0, "Outlet berhasil ditemukan", $data);
        }

        if ($request->has('owner_code')) {
            $query->where('owner_code', $request->owner_code);
        }

        // Apply sorting only by outlet_name
        if ($request->has('sort_by') && $request->sort_by == 'outlet_name') {
            $order_by = $request->order_by ?? 'ASC';
            $query->orderBy('outlets.outlet_name', $order_by);
        }

        // Apply sorting only by created_at
        if ($request->has('sort_by') && $request->sort_by == 'created_at') {
            $order_by = $request->order_by ?? 'ASC';
            $query->orderBy('outlets.created_at', $order_by);
        }

        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $data = $query->where('is_deleted', 0)
            ->leftJoin('variable_cities', 'outlets.city_code', '=', 'variable_cities.city_code')
            ->select($selects)
            ->limit($limit)
            ->offset($offset)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Outlet berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'owner_code' => 'required|max:255',
            'outlet_name' => 'required|max:255',
            'outlet_phone_number' => 'required|max:15',
            'city_code' => 'required|max:255',
            'outlet_address' => 'required|max:255',
            'outlet_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'workshop_code' => 'required|max:255',
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

        $login_user = Auth::user();
        if ($owner->owner_code != $login_user->owner_code) {
            return $this->sendError(1, "OWNER_CODE yang anda kirim tidak sesuai dengan token user yang sedang login", null);
        }

        // VALIDATE WORKSHOP_CODE
        $workshop = Workshop::where('workshop_code', $request->workshop_code)->first();
        if (!$workshop) {
            return $this->sendError(1, "Data Workshop tidak ditemukan", null);
        }

        $outlet_code = generateFiledCode('OUTLET');
        DB::beginTransaction();
        try {
            // outlet logo, save to storage
            if ($request->hasFile('outlet_logo')) {
                $outlet_logo = $request->file('outlet_logo');
                $outlet_logo_name = $outlet_code . '.' . $outlet_logo->getClientOriginalExtension();
                $outlet_logo->storeAs('public/outlet_logo', $outlet_logo_name);
                $outlet_logo_path = 'storage/outlet_logo/' . $outlet_logo_name;
            }


            $data = Outlet::create([
                'outlet_code' => $outlet_code,
                'owner_code' => $request->owner_code,
                'outlet_name' => $request->outlet_name,
                'outlet_phone_number' => $request->outlet_phone_number,
                'city_code' => $request->city_code,
                'outlet_address' => $request->outlet_address,
                'outlet_logo' => $outlet_logo_path ?? null,
                'workshop_code' => $request->workshop_code,
            ]);

            DB::commit();
            return $this->sendResponse(0, "Outlet berhasil ditambahkan", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Outlet gagal ditambahkan", $e->getMessage());
        }
    }

    public function update(Request $request, $outlet_code)
    {
        $rules = [
            'outlet_name' => 'required|max:255',
            'outlet_phone_number' => 'required|max:15',
            'city_code' => 'required|max:255',
            'outlet_address' => 'required|max:255',
            'outlet_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $outlet = Outlet::where('outlet_code', $outlet_code)->first();
        if (!$outlet) {
            return $this->sendError(1, "Data Outlet tidak ditemukan", null);
        }

        $login_user = Auth::user();
        if ($outlet->owner_code != $login_user->owner_code) {
            return $this->sendError(1, "OWNER_CODE yang anda kirim tidak sesuai dengan token user yang sedang login", [
                'owner_code' => $outlet->owner_code,
                'login_owner_code' => $login_user->owner_code,
            ]);
        }

        DB::beginTransaction();
        try {
            // outlet logo, save to storage, and delete old logo
            if ($request->hasFile('outlet_logo')) {
                $outlet_logo = $request->file('outlet_logo');
                $outlet_logo_name = $outlet_code . '.' . $outlet_logo->getClientOriginalExtension();
                $outlet_logo->storeAs('public/outlet_logo', $outlet_logo_name);
                $outlet_logo_path = 'storage/outlet_logo/' . $outlet_logo_name;

                // delete old logo
                if ($outlet->outlet_logo) {
                    $old_logo = explode('/', $outlet->outlet_logo);
                    $old_logo = end($old_logo);
                    $old_logo_path = 'public/outlet_logo/' . $old_logo;
                    if (file_exists($old_logo_path)) {
                        unlink($old_logo_path);
                    }
                }
            }

            $outlet->update([
                'outlet_name' => $request->outlet_name,
                'outlet_phone_number' => $request->outlet_phone_number,
                'city_code' => $request->city_code,
                'outlet_address' => $request->outlet_address,
                'outlet_logo' => $outlet_logo_path ?? $outlet->outlet_logo,
            ]);

            DB::commit();
            return $this->sendResponse(0, "Outlet berhasil diperbarui", $outlet);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Outlet gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($outlet_code)
    {
        $outlet = Outlet::where('outlet_code', $outlet_code)->first();
        if (!$outlet) {
            return $this->sendError(1, "Data Outlet tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $outlet->update([
                'is_deleted' => 1,
            ]);

            DB::commit();
            return $this->sendResponse(0, "Outlet berhasil dihapus", []);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Outlet gagal dihapus", $e->getMessage());
        }
    }
}
