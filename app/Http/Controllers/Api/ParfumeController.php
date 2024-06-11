<?php

namespace App\Http\Controllers\Api;

// use Log;
use Illuminate\Support\Facades\Log;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Parfume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class ParfumeController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index(Request $request)
    {
        $rules = [
            'outlet_code' => 'nullable|max:255',
            'parfume_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:outlet_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'parfume_code',
            'outlet_code',
            'parfume_name',
            'is_parfume_primary',
            'is_deleted',
        ];

        $data = Parfume::query()->where('is_deleted', 0);

        if ($request->has('parfume_code')) {

            $data = $data->where('parfume_code', $request->parfume_code)
                ->select($selects)
                ->first();

            if (!$data) {
                return $this->sendError(1, "Parfume tidak ditemukan", null);
            }

            return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
        }

        if ($request->has('outlet_code')) {
            $data->where('outlet_code', $request->outlet_code);
        }

        // search, sort_by, order_by
        if ($request->has('search')) {
            $data->where('parfume_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by')) {
            $order_by = $request->order_by ?? 'ASC';
            $data->orderBy($request->sort_by, $order_by);
        }

        // limit, offset
        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 0;

        $data = $data->select($selects)
            ->limit($limit)
            ->offset($offset)
            ->get();

        if ($data->isEmpty()) {
            return $this->sendError(1, "Parfume belum terdaftar", null);
        }

        return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    }


    public function show($parfume_code)
    {
        $selects = [
            'parfume_code',
            'outlet_code',
            'parfume_name',
            'is_parfume_primary',
            'is_deleted',
        ];

        $data = Parfume::where('parfume_code', $parfume_code)
            ->where('is_deleted', 0)
            ->select($selects)
            ->first();

        if (!$data) {
            return $this->sendError(1, "Parfume tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'parfume_name' => 'required|string|max:255',
            'is_parfume_primary' => 'required|boolean',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $is_primary_parfume = $request->is_parfume_primary;
        if ($is_primary_parfume == true) {
            $parfume = Parfume::where('outlet_code', $request->outlet_code)
                ->where('is_parfume_primary', 1)
                ->where('is_deleted', 0)
                ->first();

            if ($parfume) {
                $parfume->is_parfume_primary = 0;
                $parfume->update();
            }
        }

        $parfume = Parfume::create([
            'parfume_code' => generateFiledCode('PARFUME'),
            'outlet_code' => $request->outlet_code,
            'parfume_name' => $request->parfume_name,
            'is_parfume_primary' => $request->is_parfume_primary,
        ]);

        if (!$parfume) {
            return $this->sendError(1, "Gagal menambahkan parfume", null);
        }

        return $this->sendResponse(0, "Parfume berhasil ditambahkan", $parfume);
    }

    public function update(Request $request, $parfume_code)
    {
        $rules = [
            'parfume_name' => 'required|string|max:255',
            'is_parfume_primary' => 'required|boolean',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $parfume = Parfume::where('parfume_code', $parfume_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$parfume) {
            return $this->sendError(1, "Parfume tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            if ($request->is_parfume_primary == true) {
                $primary_parfume_of_outlet = Parfume::where('outlet_code', $parfume->outlet_code)
                    ->where('is_parfume_primary', 1)
                    ->where('is_deleted', 0)
                    ->first();

                if ($primary_parfume_of_outlet) {
                    $primary_parfume_of_outlet->is_parfume_primary = 0;
                    $primary_parfume_of_outlet->update();
                }
            }


            $parfume->parfume_name = $request->parfume_name;
            $parfume->is_parfume_primary = $request->is_parfume_primary;
            $parfume->update();

            if (!$parfume) {
                return $this->sendError(1, "Parfume gagal diperbarui", null);
            }

            DB::commit();

            return $this->sendResponse(0, "Parfume berhasil diperbarui", $parfume);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Parfume gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($parfume_code)
    {
        $parfume = Parfume::where('parfume_code', $parfume_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$parfume) {
            return $this->sendError(1, "Parfume tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $parfume->is_deleted = 1;
            $parfume->update();

            if (!$parfume) {
                return $this->sendError(1, "Parfume gagal dihapus", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Parfume berhasil dihapus", $parfume);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Parfume gagal dihapus", $e->getMessage());
        }
    }

    public function getByOutlet($outlet_code)
    {
        $selects = [
            'parfume_code',
            'outlet_code',
            'parfume_name',
            'is_parfume_primary',
            'is_deleted',
        ];

        $data = Parfume::where('outlet_code', $outlet_code)
            ->where('is_deleted', 0)
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Outlet ini belum membuat parfume", null);
        }

        return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    }
}
