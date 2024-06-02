<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Owner;
use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\TopupRepository;
use App\Http\Repositories\LogBalanceRepository;

class TopupController extends ApiController
{

    protected $topupRepository;
    protected $logBalanceRepository;

    public function __construct(LogBalanceRepository $logBalanceRepository, TopupRepository $topupRepository)
    {
        $this->logBalanceRepository = $logBalanceRepository;
        $this->topupRepository = $topupRepository;
    }

    public function create(Request $request)
    {
        $rules = [
            'owner_code' => 'required',
            'bank_code' => 'required',
            'topup_amount' => 'required|numeric',
            'topup_amount_unique_code' => 'required|numeric',
        ];

        // T

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        try {
            $data_owner = Owner::where('owner_code', $request->owner_code)->first();
            if(!$data_owner) {
                return $this->sendError(2, 'Data owner tidak ditemukan');
            }

            $topup = $this->topupRepository->create($request->owner_code, $request->bank_code, $request->topup_amount, $request->topup_amount_unique_code);

            return $this->sendResponse(0, "Berhasil login ke dalam aplikasi", $topup);
        } catch (\Exception $e) {
            return $this->sendError(2, "Gagal login ke dalam aplikasi", $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $rules = [
            'topup_code' => 'required',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(1, 'Params not complete', validationMessage($validator->errors()));
        }

        try {

            $topup = $this->topupRepository->updateStatus($request->topup_code, 'SUCCESS');
            $log_balance = $this->logBalanceRepository->create($topup->owner_code, $topup->topup_amount, 'TOPUP', 'IN');

            return $this->sendResponse(0, "Berhasil login ke dalam aplikasi", $topup);
        } catch (\Exception $e) {
            return $this->sendError(2, "Gagal login ke dalam aplikasi", $e->getMessage());
        }
    }

    // === CRONJOB, AUTO CANCEL IF TOP UP MORE THAN 1 DAY ===
    public function autoCancel()
    {
        $topups = Topup::where('topup_status', 'PENDING')->where('created_at', '<', Carbon::now()->subDay())->get();
        foreach($topups as $topup) {
            $topup->topup_status = 'CANCEL';
            $topup->update();
        }

        return $this->sendResponse(0, "Berhasil cancel topup", $topups);
    }
    // === END CRONJOB, AUTO CANCEL IF TOP UP MORE THAN 1 DAY ===

}
