<?php

namespace App\Http\Repositories;

use App\Models\Owner;
use App\Models\Topup;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class TopupRepository extends ApiController
{
    public function create($owner_code, $bank_code, $topup_amount, $topup_amount_unique_code)
    {
        $topup_code = generateFiledCode('TOPUP');
        $topup_status = 'PENDING';

        DB::beginTransaction();
        try {
            $topup = Topup::create([
                'topup_code' => $topup_code,
                'owner_code' => $owner_code,
                'bank_code' => $bank_code,
                'topup_amount' => $topup_amount,
                'topup_amount_unique_code' => $topup_amount_unique_code,
                'topup_status' => $topup_status
            ]);

            DB::commit();

            return $topup;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to create topup', $e->getMessage());
        }
    }

    public function updateStatus($topup_code, $status)
    {
        DB::beginTransaction();
        try {
            $topup = Topup::where('topup_code', $topup_code)->first();
            if(!$topup) {
                return $this->sendError(2, 'Topup not found');
            }

            $topup->topup_status = $status;
            $topup->update();

            DB::commit();

            return $topup;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to update topup status', $e->getMessage());
        }
    }
}
