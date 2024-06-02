<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class VariableController extends ApiController
{
    public function cities()
    {
        $selects = [
            'city_code',
            'city_name',
        ];

        $data = DB::table('variable_cities')->select($selects)->get();
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


}
