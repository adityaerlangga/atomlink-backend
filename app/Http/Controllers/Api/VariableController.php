<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\VariableCities;

class VariableController extends ApiController
{
    public function cities(Request $request)
    {
        $rules = [
            'city_code' => 'nullable|max:255',
            'sort_by' => 'nullable|in:city_name,created_at',
            'order_by' => 'nullable|in:ASC,DESC',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $selects = [
            'city_code',
            'city_name',
        ];

        $data = VariableCities::query();

        // search, limit, and offset
        if ($request->has('search')) {
            $data = $data->where('city_name', 'like', '%' . $request->search . '%');
        }


        $limit = $request->has('limit') ? $request->limit : 20;
        $offset = $request->has('offset') ? $request->offset : 0;

        $data = $data->orderBy('city_name', 'ASC');

        $data = $data->select($selects)->limit($limit)->offset($offset)->get();

        return $this->sendResponse(0, "DAFTAR KOTA berhasil ditemukan", $data);
    }

    public function units()
    {
        $selects = [
            'unit_code',
            'unit_name',
        ];

        $data = DB::table('variable_units')->select($selects)->get();
        return $this->sendResponse(0, "DAFTAR SATUAN berhasil ditemukan", $data);
    }


    public function banks()
    {
        $selects = [
            'bank_code',
            'bank_name',
            'bank_logo',
            'bank_account_number',
            'bank_account_holder',
        ];

        $data = DB::table('variable_banks')->select($selects)->get();
        return $this->sendResponse(0, "DAFTAR BANK berhasil ditemukan", $data);
    }

    public function service_categories()
    {
        $selects = [
            'service_category_code',
            'service_category_name',
        ];

        $data = DB::table('variable_service_categories')->select($selects)->get();
        return $this->sendResponse(0, "DAFTAR KATEGORI LAYANAN berhasil ditemukan", $data);
    }
}
