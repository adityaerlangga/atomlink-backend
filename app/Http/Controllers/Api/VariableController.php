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
