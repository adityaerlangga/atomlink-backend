<?php

namespace App\Http\Controllers\Api;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Owner;

class DashboardController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    // CHANGES TEST FOR PULLING GIT
    // COUNTS: 5
}
