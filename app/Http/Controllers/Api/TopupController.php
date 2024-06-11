<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\CoinRepository;
use App\Http\Repositories\LogBalanceRepository;
use App\Http\Repositories\TopupRepository;

class TopupController extends ApiController
{

    protected $coinRepository;
    protected $topupRepository;

    public function __construct(CoinRepository $coinRepository, TopupRepository $topupRepository)
    {
        $this->coinRepository = $coinRepository;
        $this->topupRepository = $topupRepository;
    }

    public function all()
    {
        $topups = $this->topupRepository->all();

        return $this->sendResponse(0, "Berhasil mendapatkan data topup", $topups);
    }

    public function getByOwner($owner_code)
    {
        $topups = $this->topupRepository->getByOwner($owner_code);

        if($topups->isEmpty()) {
            return $this->sendError(1, "Data topup tidak ditemukan");
        }

        return $this->sendResponse(0, "Berhasil mendapatkan data topup", $topups);
    }

    public function create(Request $request)
    {
        $rules = [
            'owner_code' => 'required',
            'bank_code' => 'required',
            'topup_amount' => 'required|numeric'
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        try {
            $data_owner = Owner::where('owner_code', $request->owner_code)->first();
            if(!$data_owner) {
                return $this->sendError(2, 'Data owner tidak ditemukan');
            }

            $owner_code = $request->owner_code;
            $bank_code = $request->bank_code;
            $topup_amount = $request->topup_amount;
            $topup_amount_unique_code = rand(100, 999);

            $topup = $this->topupRepository->create($owner_code, $bank_code, $topup_amount, $topup_amount_unique_code);

            return $this->sendResponse(0, "Berhasil login ke dalam aplikasi", $topup);
        } catch (\Exception $e) {
            return $this->sendError(2, "Gagal login ke dalam aplikasi", $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $rules = [
            'topup_code' => 'required',
            'topup_secret_key' => 'required'
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $topup_secret_key = env('TOPUP_SECRET_KEY', 'DEV_SECRET_KEY');
        if($request->topup_secret_key != $topup_secret_key) {
            return $this->sendError(2, 'Secret key not valid');
        }

        try {
            // UPDATE TOPUP STATUS
            $topup = $this->topupRepository->payment_success($request->topup_code);

            // UPDATE OWNER BALANCE
            $type = 'INCOME';
            $categories = 'TOPUP';
            $amount = $topup->topup_amount;
            $description = 'TOPUP ' . $topup->topup_code .  'pada tanggal ' . $topup->created_at->format('Y-m-d H:i:s') . ' sebesar Rp' . number_format($topup->topup_amount + $topup->topup_amount_unique_code, 0, ',', '.');

            $this->coinRepository->updateBalance($topup->owner_code, $type, $categories, $amount, $description);


            return $this->sendResponse(0, "TOPUP_CODE: $request->topup_code, Berhasil dilakukan, saldo owner telah diupdate", []);

        } catch (\Exception $e) {
        }
    }

    // === CRONJOB, AUTO CANCEL IF TOP UP MORE THAN 1 DAY ===
    public function autoCancel()
    {
        $topups = Coin::where('topup_status', 'PENDING')->where('created_at', '<', Carbon::now()->subDay())->get();
        foreach($topups as $topup) {
            $topup->topup_status = 'CANCEL';
            $topup->update();
        }

        return $this->sendResponse(0, "Berhasil cancel topup", $topups);
    }
    // === END CRONJOB, AUTO CANCEL IF TOP UP MORE THAN 1 DAY ===

}
