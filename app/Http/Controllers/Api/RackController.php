<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Parfume;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RackCategory;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class RackController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index(Request $request)
    {
        $rules = [
            'outlet_code' => 'nullable|max:255',
            'rack_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:rack_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'racks.rack_code',
            'racks.outlet_code',
            'racks.rack_category_code',
            'rack_categories.rack_category_name',
            'racks.rack_name',
            'racks.is_deleted',
        ];

        $data = Rack::query()->where('racks.is_deleted', 0);

        if ($request->has('rack_code')) {
            $data = $data->where('racks.rack_code', $request->rack_code)
                ->leftJoin('rack_categories', 'racks.rack_category_code', '=', 'rack_categories.rack_category_code')
                ->select($selects)
                ->first();

            if (!$data) {
                return $this->sendError(1, "Rack tidak ditemukan", null);
            }

            return $this->sendResponse(0, "Rack berhasil ditemukan", $data);
        }

        if ($request->has('outlet_code')) {
            $data->where('racks.outlet_code', $request->outlet_code);
        }

        // search, sort_by, order_by
        if ($request->has('search')) {
            $data->where('racks.rack_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort_by')) {
            $order_by = $request->order_by ?? 'ASC';
            $data->orderBy('racks.' . $request->sort_by, $order_by);
        }

        // limit, offset
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $data = $data->leftJoin('rack_categories', 'racks.rack_category_code', '=', 'rack_categories.rack_category_code')
            ->select($selects)
            ->limit($limit)
            ->offset($offset)
            ->get();
        

        if (!$data) {
            return $this->sendError(1, "Rack tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Rack belum terdaftar", null);
        }

        return $this->sendResponse(0, "Rack berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'rack_category_name' => 'nullable|string|max:255',
            'rack_name' => 'required|string|max:255',
            'total_rack' => 'nullable|integer|min:1'
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        if ($request->rack_category_name) {
            $rack_category = DB::table('rack_categories')
                ->where('rack_category_name', $request->rack_category_name)
                ->first();

            if (!$rack_category) {
                $rack_category_code = generateFiledCode('RACK_CATEGORY');

                $rack_category = RackCategory::create([
                    'outlet_code' => $request->outlet_code, 
                    'rack_category_code' => $rack_category_code,
                    'rack_category_name' => $request->rack_category_name,
                ]);

                if (!$rack_category) {
                    return $this->sendError(1, "Gagal menambahkan Rack Category", null);
                }
            } else {
                $rack_category_code = $rack_category->rack_category_code;
            }
        }

        $total_rack = $request->total_rack ?? 1;
        $data = [];
        while($total_rack > 0) {
            $rack_name = $total_rack >= 1 ? $request->rack_name . " " . $total_rack : $request->rack_name;
            $rack = Rack::create([
                'rack_code' => generateFiledCode('RACK'),
                'outlet_code' => $request->outlet_code,
                'rack_category_code' => $rack_category_code ?? null,
                'rack_name' => $rack_name,
            ]);
            
            if (!$rack) {
                return $this->sendError(1, "Gagal menambahkan Rack", null);
            }

            $data[] = $rack;

            $total_rack--;
        }

        if (!$rack) {
            return $this->sendError(1, "Gagal menambahkan Rack", null);
        }

        return $this->sendResponse(0, "Rack berhasil ditambahkan", $data);
    }

    public function update(Request $request, $rack_code)
    {
        $rules = [
            'rack_name' => 'required|string|max:255',
            'rack_category_name' => 'nullable|string|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $rack = Rack::where('rack_code', $rack_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$rack) {
            return $this->sendError(1, "Rack tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            if ($request->rack_category_name) {
                $rack_category = DB::table('rack_categories')
                    ->where('rack_category_name', $request->rack_category_name)
                    ->first();

                if (!$rack_category) {
                    $rack_category_code = generateFiledCode('RACK_CATEGORY');

                    $rack_category = RackCategory::create([
                        'outlet_code' => $rack->outlet_code, 
                        'rack_category_code' => $rack_category_code,
                        'rack_category_name' => $request->rack_category_name,
                    ]);

                    if (!$rack_category) {
                        return $this->sendError(1, "Gagal menambahkan Rack Category", null);
                    }
                } else {
                    $rack_category_code = $rack_category->rack_category_code;
                }
            }

            $rack->rack_name = $request->rack_name;
            $rack->rack_category_code = $rack_category_code ?? null;
            $rack->update();

            if (!$rack) {
                return $this->sendError(1, "Rack gagal diperbarui", null);
            }

            DB::commit();

            return $this->sendResponse(0, "Rack berhasil diperbarui", $rack);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Rack gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($rack_code)
    {
        $rack = Rack::where('rack_code', $rack_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$rack) {
            return $this->sendError(1, "Rack tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $rack->is_deleted = 1;
            $rack->update();

            if (!$rack) {
                return $this->sendError(1, "Rack gagal dihapus", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Rack berhasil dihapus", $rack);
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Rack gagal dihapus", $e->getMessage());
        }
    }

    public function getByOutlet($outlet_code)
    {
        $selects = [
            'racks.rack_code',
            'racks.outlet_code',
            'racks.rack_category_code',
            'rack_categories.rack_category_name',
            'racks.rack_name',
            'racks.is_deleted',
        ];

        $data = DB::table('racks')
            ->leftJoin('rack_categories', 'racks.rack_category_code', '=', 'rack_categories.rack_category_code')
            ->where('racks.outlet_code', $outlet_code)
            ->where('racks.is_deleted', 0)
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Outlet ini belum membuat Rack", null);
        }

        return $this->sendResponse(0, "Rack berhasil ditemukan", $data);
    }
}
