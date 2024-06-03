<?php

namespace App\Http\Controllers\Api;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Owner;

class CustomerController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function index()
    {
        $selects = [
            'customers.customer_code',
            'customers.outlet_code',
            'customers.customer_name',
            'customers.customer_gender',
            'customers.customer_title',
            'customers.customer_whatsapp_number',
            'customers.is_customer_have_addresses',
            'customers.customer_institution',
            'customers.customer_birth_date',
            'customers.customer_religion',
            'customers.customer_email',
            'customer_addresses.customer_address_code',

            'customer_addresses.customer_full_address',
            'customer_addresses.customer_address_latitude',
            'customer_addresses.customer_address_longitude',
            'customer_addresses.customer_address_location_name',
            'customer_addresses.customer_address_label',
            'customer_addresses.is_customer_address_primary',
        ];

        $data = Customer::where('customers.is_deleted', 0)
            ->leftJoin('customer_addresses', 'customers.customer_code', '=', 'customer_addresses.customer_code')
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Customer tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Belum ada customer yang terdaftar", null);
        }

        return $this->sendResponse(0, "Customer berhasil ditemukan", $data);
    }

    public function show($customer_code)
    {
        $selects = [
            'customers.customer_code',
            'customers.outlet_code',
            'customers.customer_name',
            'customers.customer_gender',
            'customers.customer_title',
            'customers.customer_whatsapp_number',
            'customers.is_customer_have_addresses',
            'customers.customer_institution',
            'customers.customer_birth_date',
            'customers.customer_religion',
            'customers.customer_email',
            'customer_addresses.customer_address_code',
            'customer_addresses.customer_full_address',
            'customer_addresses.customer_address_latitude',
            'customer_addresses.customer_address_longitude',
            'customer_addresses.customer_address_location_name',
            'customer_addresses.customer_address_label',
            'customer_addresses.is_customer_address_primary',
        ];

        $data = Customer::where('customers.customer_code', $customer_code)
            ->where('customers.is_deleted', 0)
            ->leftJoin('customer_addresses', 'customers.customer_code', '=', 'customer_addresses.customer_code')
            ->select($selects)
            ->first();

        if (!$data) {
            return $this->sendError(1, "Customer tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Customer berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|max:255',
            'customer_name' => 'required|max:255',
            'customer_gender' => 'required|max:255',
            'customer_title' => 'required|max:255',
            'customer_whatsapp_number' => 'max:255',
            'is_customer_have_addresses' => 'required|boolean',
            'customer_institution' => 'max:255',
            'customer_birth_date' => 'max:255',
            'customer_religion' => 'max:255',
            'customer_email' => 'max:255',

            // CUSTOMER ADDRESS
            'customer_full_address' => 'required_if:is_customer_have_addresses,1|max:255',
            'customer_address_latitude' => 'required_if:customer_address_longitude,1|max:255',
            'customer_address_longitude' => 'required_if:customer_address_latitude,1|max:255',
            'customer_address_location_name' => 'max:255',
            'customer_address_label' => 'max:255',
            'is_customer_address_primary' => 'required_if:is_customer_have_addresses,1|boolean',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        // VALIDATE OUTLET_CODE
        $outlet = Outlet::where('outlet_code', $request->outlet_code)->first();
        if (!$outlet) {
            return $this->sendError(1, "Data Outlet tidak ditemukan", null);
        }

        $customer_code = generateFiledCode('CUST');
        DB::beginTransaction();
        try {
            $data = Customer::create([
                'customer_code' => $customer_code,
                'outlet_code' => $request->outlet_code,
                'customer_name' => $request->customer_name,
                'customer_gender' => $request->customer_gender,
                'customer_title' => $request->customer_title,
                'customer_whatsapp_number' => $request->customer_whatsapp_number ?? null,
                'is_customer_have_addresses' => $request->is_customer_have_addresses ?? false,
                'customer_institution' => $request->customer_institution ?? null,
                'customer_birth_date' => $request->customer_birth_date ?? null,
                'customer_religion' => $request->customer_religion ?? null,
                'customer_email' => $request->customer_email ?? null,
            ]);

            if($request->is_customer_have_addresses == true) {
                // CREATE CUSTOMER ADDRESS
                $customer_address_code = generateFiledCode('CUST_ADDR');
                $data = CustomerAddress::create([
                    'customer_address_code' => $customer_address_code,
                    'customer_code' => $customer_code,
                    'customer_full_address' => $request->customer_full_address,
                    'customer_address_latitude' => $request->customer_address_latitude ?? null,
                    'customer_address_longitude' => $request->customer_address_longitude ?? null,
                    'customer_address_location_name' => $request->customer_address_location_name ?? null,
                    'customer_address_label' => $request->customer_address_label ?? null,
                    'is_customer_address_primary' => $request->is_customer_address_primary ?? false,
                ]);
            }

            DB::commit();
            return $this->sendResponse(0, "Outlet berhasil ditambahkan", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Outlet gagal ditambahkan", $e->getMessage());
        }
    }

    public function update(Request $request, $customer_code)
    {
        $rules = [
            'customer_name' => 'required|max:255',
            'customer_gender' => 'required|max:255',
            'customer_title' => 'required|max:255',
            'customer_whatsapp_number' => 'max:255',
            'customer_institution' => 'max:255',
            'customer_birth_date' => 'max:255',
            'customer_religion' => 'max:255',
            'customer_email' => 'max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $customer = Customer::where('customer_code', $customer_code)->first();

        if (!$customer) {
            return $this->sendError(1, "Data Customer tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $customer->update([
                'customer_name' => $request->customer_name,
                'customer_gender' => $request->customer_gender,
                'customer_title' => $request->customer_title,
                'customer_whatsapp_number' => $request->customer_whatsapp_number ?? null,
                'is_customer_have_addresses' => $request->is_customer_have_addresses ?? false,
                'customer_institution' => $request->customer_institution ?? null,
                'customer_birth_date' => $request->customer_birth_date ?? null,
                'customer_religion' => $request->customer_religion ?? null,
                'customer_email' => $request->customer_email ?? null,
            ]);

            DB::commit();

            return $this->sendResponse(0, "Customer berhasil diperbarui", $customer);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Customer gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($customer_code)
    {
        $customer = Customer::where('customer_code', $customer_code)->first();

        if (!$customer) {
            return $this->sendError(1, "Data Customer tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $customer->update([
                'is_deleted' => true,
            ]);

            DB::commit();

            return $this->sendResponse(0, "Customer berhasil dihapus", []);
            // dd('OK');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Customer gagal dihapus", $e->getMessage());
        }
    }

    public function getOwnerCustomers($owner_code)
    {
        $selects = [
            'customers.customer_code',
            'customers.outlet_code',
            'customers.customer_name',
            'customers.customer_gender',
            'customers.customer_title',
            'customers.customer_whatsapp_number',
            'customers.is_customer_have_addresses',
            'customers.customer_institution',
            'customers.customer_birth_date',
            'customers.customer_religion',
            'customers.customer_email',
            'customer_addresses.customer_address_code',
            'customer_addresses.customer_full_address',
            'customer_addresses.customer_address_latitude',
            'customer_addresses.customer_address_longitude',
            'customer_addresses.customer_address_location_name',
            'customer_addresses.customer_address_label',
            'customer_addresses.is_customer_address_primary',
        ];

        // VALIDATE OWNER_CODE
        $owner = Owner::where('owner_code', $owner_code)->first();
        if (!$owner) {
            return $this->sendError(1, "Data Owner tidak ditemukan", null);
        }

        // GET ALL OUTLETS OF OWNER
        $outlets = Outlet::where('owner_code', $owner_code)->pluck('outlet_code')->toArray();

        if(!$outlets) {
            return $this->sendError(1, "Owner belum memiliki Outlet yang terdaftar", null);
        }

        $data = Customer::whereIn('outlet_code', $outlets)
                ->leftJoin('customer_addresses', 'customers.customer_code', '=', 'customer_addresses.customer_code')
                ->select($selects)
                ->get();

        if (!$data) {
            return $this->sendError(1, "Customer tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Owner belum memiliki Customer yang terdaftar", null);
        }

        return $this->sendResponse(0, "Customer berhasil ditemukan", $data);
    }

    public function getOutletCustomers($outlet_code)
    {
        $selects = [
            'customers.customer_code',
            'customers.outlet_code',
            'customers.customer_name',
            'customers.customer_gender',
            'customers.customer_title',
            'customers.customer_whatsapp_number',
            'customers.is_customer_have_addresses',
            'customers.customer_institution',
            'customers.customer_birth_date',
            'customers.customer_religion',
            'customers.customer_email',
            'customer_addresses.customer_address_code',
            'customer_addresses.customer_full_address',
            'customer_addresses.customer_address_latitude',
            'customer_addresses.customer_address_longitude',
            'customer_addresses.customer_address_location_name',
            'customer_addresses.customer_address_label',
            'customer_addresses.is_customer_address_primary',
        ];

        // GET ALL OUTLETS OF OWNER
        $outlets = Outlet::where('outlet_code', $outlet_code)->pluck('outlet_code')->toArray();

        if(!$outlets) {
            return $this->sendError(1, "Owner belum memiliki Outlet yang terdaftar", null);
        }

        $data = Customer::whereIn('outlet_code', $outlets)
                ->leftJoin('customer_addresses', 'customers.customer_code', '=', 'customer_addresses.customer_code')
                ->select($selects)
                ->get();

        if (!$data) {
            return $this->sendError(1, "Customer tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Owner belum memiliki Customer yang terdaftar", null);
        }

        return $this->sendResponse(0, "Customer berhasil ditemukan", $data);
    }
}
