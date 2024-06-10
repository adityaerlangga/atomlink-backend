<?php

namespace App\Http\Controllers\Api;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Parfume;
use App\Models\ProductRack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductRackCategory;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class ProductRackController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    // public function index()
    // {
    //     $selects = [
    //         'parfume_code',
    //         'outlet_code',
    //         'parfume_name',
    //         'is_parfume_primary',
    //         'is_deleted',
    //     ];

    //     $data = Parfume::where('is_deleted', 0)
    //         ->select($selects)
    //         ->get();

    //     if (!$data) {
    //         return $this->sendError(1, "Parfume tidak ditemukan", null);
    //     }

    //     if ($data->isEmpty()) {
    //         return $this->sendError(1, "Parfume belum terdaftar", null);
    //     }

    //     return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    // }

    // public function show($parfume_code)
    // {
    //     $selects = [
    //         'parfume_code',
    //         'outlet_code',
    //         'parfume_name',
    //         'is_parfume_primary',
    //         'is_deleted',
    //     ];

    //     $data = Parfume::where('parfume_code', $parfume_code)
    //         ->where('is_deleted', 0)
    //         ->select($selects)
    //         ->first();

    //     if (!$data) {
    //         return $this->sendError(1, "Parfume tidak ditemukan", null);
    //     }

    //     return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    // }

    // public function store(Request $request)
    // {
    //     $rules = [
    //         'outlet_code' => 'required|string|max:255',
    //         'parfume_name' => 'required|string|max:255',
    //         'is_parfume_primary' => 'required|boolean',
    //     ];

    //     $validator = validateThis($request, $rules);

    //     if ($validator->fails()) {
    //         return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
    //     }

    //     $is_primary_parfume = $request->is_parfume_primary;
    //     if($is_primary_parfume == true) {
    //         $parfume = Parfume::where('outlet_code', $request->outlet_code)
    //             ->where('is_parfume_primary', 1)
    //             ->where('is_deleted', 0)
    //             ->first();

    //         if ($parfume) {
    //             $parfume->is_parfume_primary = 0;
    //             $parfume->update();
    //         }
    //     }

    //     $parfume = Parfume::create([
    //         'parfume_code' => generateFiledCode('PARFUME'),
    //         'outlet_code' => $request->outlet_code,
    //         'parfume_name' => $request->parfume_name,
    //         'is_parfume_primary' => $request->is_parfume_primary,
    //     ]);

    //     if (!$parfume) {
    //         return $this->sendError(1, "Gagal menambahkan parfume", null);
    //     }

    //     return $this->sendResponse(0, "Parfume berhasil ditambahkan", $parfume);
    // }

    // public function update(Request $request, $parfume_code)
    // {
    //     $rules = [
    //         'parfume_name' => 'required|string|max:255',
    //         'is_parfume_primary' => 'required|boolean',
    //     ];

    //     $validator = validateThis($request, $rules);

    //     if ($validator->fails()) {
    //         return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
    //     }

    //     $parfume = Parfume::where('parfume_code', $parfume_code)
    //         ->where('is_deleted', 0)
    //         ->first();

    //     if (!$parfume) {
    //         return $this->sendError(1, "Parfume tidak ditemukan", null);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         if($request->is_parfume_primary == true) {
    //             $primary_parfume_of_outlet = Parfume::where('outlet_code', $parfume->outlet_code)
    //                 ->where('is_parfume_primary', 1)
    //                 ->where('is_deleted', 0)
    //                 ->first();

    //             if ($primary_parfume_of_outlet) {
    //                 $primary_parfume_of_outlet->is_parfume_primary = 0;
    //                 $primary_parfume_of_outlet->update();
    //             }
    //         }


    //         $parfume->parfume_name = $request->parfume_name;
    //         $parfume->is_parfume_primary = $request->is_parfume_primary;
    //         $parfume->update();

    //         if (!$parfume) {
    //             return $this->sendError(1, "Parfume gagal diperbarui", null);
    //         }

    //         DB::commit();

    //         return $this->sendResponse(0, "Parfume berhasil diperbarui", $parfume);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->sendError(2, "Parfume gagal diperbarui", $e->getMessage());
    //     }

    // }

    // public function destroy($parfume_code)
    // {
    //     $parfume = Parfume::where('parfume_code', $parfume_code)
    //         ->where('is_deleted', 0)
    //         ->first();

    //     if (!$parfume) {
    //         return $this->sendError(1, "Parfume tidak ditemukan", null);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $parfume->is_deleted = 1;
    //         $parfume->update();

    //         if (!$parfume) {
    //             return $this->sendError(1, "Parfume gagal dihapus", null);
    //         }

    //         DB::commit();
    //         return $this->sendResponse(0, "Parfume berhasil dihapus", $parfume);
    //     }catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->sendError(2, "Parfume gagal dihapus", $e->getMessage());
    //     }
    // }

    // public function getByOutlet($outlet_code)
    // {
    //     $selects = [
    //         'parfume_code',
    //         'outlet_code',
    //         'parfume_name',
    //         'is_parfume_primary',
    //         'is_deleted',
    //     ];

    //     $data = Parfume::where('outlet_code', $outlet_code)
    //         ->where('is_deleted', 0)
    //         ->select($selects)
    //         ->get();

    //     if (!$data) {
    //         return $this->sendError(1, "Outlet tidak ditemukan", null);
    //     }

    //     if ($data->isEmpty()) {
    //         return $this->sendError(1, "Outlet ini belum membuat parfume", null);
    //     }

    //     return $this->sendResponse(0, "Parfume berhasil ditemukan", $data);
    // }

    // $table->string('product_rack_code', 255)->unique()->index();
    // $table->string('outlet_code', 255)->index(); // FROM OUTLET TABLE
    // $table->string('product_rack_category_code', 255)->index()->nullable(); // FROM PRODUCT_RACK_CATEGORIES TABLE
    // $table->string('product_rack_name', 255);
    // $table->boolean('is_deleted')->default(false);

    public function index()
    {
        $selects = [
            'product_racks.product_rack_code',
            'product_racks.outlet_code',
            'product_racks.product_rack_category_code',
            'product_rack_categories.product_rack_category_name',
            'product_racks.product_rack_name',
            'product_racks.is_deleted',
        ];

        $data = DB::table('product_racks')
            ->leftJoin('product_rack_categories', 'product_racks.product_rack_category_code', '=', 'product_rack_categories.product_rack_category_code')
            ->where('product_racks.is_deleted', 0)
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Product Rack tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Product Rack belum terdaftar", null);
        }

        return $this->sendResponse(0, "Product Rack berhasil ditemukan", $data);
    }

    public function show($product_rack_code)
    {
        $selects = [
            'product_racks.product_rack_code',
            'product_racks.outlet_code',
            'product_racks.product_rack_category_code',
            'product_rack_categories.product_rack_category_name',
            'product_racks.product_rack_name',
            'product_racks.is_deleted',
        ];

        $data = DB::table('product_racks')
            ->leftJoin('product_rack_categories', 'product_racks.product_rack_category_code', '=', 'product_rack_categories.product_rack_category_code')
            ->where('product_racks.product_rack_code', $product_rack_code)
            ->where('product_racks.is_deleted', 0)
            ->select($selects)
            ->first();

        if (!$data) {
            return $this->sendError(1, "Product Rack tidak ditemukan", null);
        }

        return $this->sendResponse(0, "Product Rack berhasil ditemukan", $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'outlet_code' => 'required|string|max:255',
            'product_rack_category_name' => 'nullable|string|max:255',
            'product_rack_name' => 'required|string|max:255',
            'total_rack' => 'nullable|integer|min:1'
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        if ($request->product_rack_category_name) {
            $product_rack_category = DB::table('product_rack_categories')
                ->where('product_rack_category_name', $request->product_rack_category_name)
                ->first();

            if (!$product_rack_category) {
                $product_rack_category_code = generateFiledCode('PRODUCT_RACK_CATEGORY');

                $product_rack_category = ProductRackCategory::create([
                    'outlet_code' => $request->outlet_code, 
                    'product_rack_category_code' => $product_rack_category_code,
                    'product_rack_category_name' => $request->product_rack_category_name,
                ]);

                if (!$product_rack_category) {
                    return $this->sendError(1, "Gagal menambahkan Product Rack Category", null);
                }
            } else {
                $product_rack_category_code = $product_rack_category->product_rack_category_code;
            }
        }

        $total_rack = $request->total_rack ?? 1;
        $data = [];
        while($total_rack > 0) {
            $product_rack_name = $total_rack >= 1 ? $request->product_rack_name . " " . $total_rack : $request->product_rack_name;
            $product_rack = ProductRack::create([
                'product_rack_code' => generateFiledCode('PRODUCT_RACK'),
                'outlet_code' => $request->outlet_code,
                'product_rack_category_code' => $product_rack_category_code ?? null,
                'product_rack_name' => $product_rack_name,
            ]);
            
            if (!$product_rack) {
                return $this->sendError(1, "Gagal menambahkan Product Rack", null);
            }

            $data[] = $product_rack;

            $total_rack--;
        }

        if (!$product_rack) {
            return $this->sendError(1, "Gagal menambahkan Product Rack", null);
        }

        return $this->sendResponse(0, "Product Rack berhasil ditambahkan", $data);
    }

    public function update(Request $request, $product_rack_code)
    {
        $rules = [
            'product_rack_name' => 'required|string|max:255',
            'product_rack_category_name' => 'nullable|string|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        $product_rack = ProductRack::where('product_rack_code', $product_rack_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$product_rack) {
            return $this->sendError(1, "Product Rack tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            if ($request->product_rack_category_name) {
                $product_rack_category = DB::table('product_rack_categories')
                    ->where('product_rack_category_name', $request->product_rack_category_name)
                    ->first();

                if (!$product_rack_category) {
                    $product_rack_category_code = generateFiledCode('PRODUCT_RACK_CATEGORY');

                    $product_rack_category = ProductRackCategory::create([
                        'outlet_code' => $product_rack->outlet_code, 
                        'product_rack_category_code' => $product_rack_category_code,
                        'product_rack_category_name' => $request->product_rack_category_name,
                    ]);

                    if (!$product_rack_category) {
                        return $this->sendError(1, "Gagal menambahkan Product Rack Category", null);
                    }
                } else {
                    $product_rack_category_code = $product_rack_category->product_rack_category_code;
                }
            }

            $product_rack->product_rack_name = $request->product_rack_name;
            $product_rack->product_rack_category_code = $product_rack_category_code ?? null;
            $product_rack->update();

            if (!$product_rack) {
                return $this->sendError(1, "Product Rack gagal diperbarui", null);
            }

            DB::commit();

            return $this->sendResponse(0, "Product Rack berhasil diperbarui", $product_rack);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Product Rack gagal diperbarui", $e->getMessage());
        }
    }

    public function destroy($product_rack_code)
    {
        $product_rack = ProductRack::where('product_rack_code', $product_rack_code)
            ->where('is_deleted', 0)
            ->first();

        if (!$product_rack) {
            return $this->sendError(1, "Product Rack tidak ditemukan", null);
        }

        DB::beginTransaction();
        try {
            $product_rack->is_deleted = 1;
            $product_rack->update();

            if (!$product_rack) {
                return $this->sendError(1, "Product Rack gagal dihapus", null);
            }

            DB::commit();
            return $this->sendResponse(0, "Product Rack berhasil dihapus", $product_rack);
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(2, "Product Rack gagal dihapus", $e->getMessage());
        }
    }

    public function getByOutlet($outlet_code)
    {
        $selects = [
            'product_racks.product_rack_code',
            'product_racks.outlet_code',
            'product_racks.product_rack_category_code',
            'product_rack_categories.product_rack_category_name',
            'product_racks.product_rack_name',
            'product_racks.is_deleted',
        ];

        $data = DB::table('product_racks')
            ->leftJoin('product_rack_categories', 'product_racks.product_rack_category_code', '=', 'product_rack_categories.product_rack_category_code')
            ->where('product_racks.outlet_code', $outlet_code)
            ->where('product_racks.is_deleted', 0)
            ->select($selects)
            ->get();

        if (!$data) {
            return $this->sendError(1, "Outlet tidak ditemukan", null);
        }

        if ($data->isEmpty()) {
            return $this->sendError(1, "Outlet ini belum membuat Product Rack", null);
        }

        return $this->sendResponse(0, "Product Rack berhasil ditemukan", $data);
    }
}
