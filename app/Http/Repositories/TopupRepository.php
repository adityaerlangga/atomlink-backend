<?php

namespace App\Http\Repositories;

use App\Models\Owner;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\Topup;

class TopupRepository extends ApiController
{
    protected $coinRepository;

    public function __construct(CoinRepository $coinRepository)
    {
        $this->coinRepository = $coinRepository;
    }

    public function all()
    {
        $selects = [
            'topups.topup_code',
            'topups.owner_code',
            'topups.bank_code',
            'topups.topup_amount',
            'topups.topup_amount_unique_code',
            'topups.topup_status',
            'topups.created_at',
            'topups.updated_at',
            'variable_banks.bank_name',
        ];

        // left join variable_variable_banks
        $topups = Topup::select($selects)
            ->leftJoin('variable_banks', 'topups.bank_code', '=', 'variable_banks.bank_code')
            ->get();

        return $topups;
    }

    public function getByOwner($owner_code)
    {
        $selects = [
            'topups.topup_code',
            'topups.owner_code',
            'topups.bank_code',
            'topups.topup_amount',
            'topups.topup_amount_unique_code',
            'topups.topup_status',
            'topups.created_at',
            'topups.updated_at',
            'variable_banks.bank_name',
        ];

        // left join variable_variable_banks
        $topups = Topup::select($selects)
            ->leftJoin('variable_banks', 'topups.bank_code', '=', 'variable_banks.bank_code')
            ->where('topups.owner_code', $owner_code)
            ->get();

        return $topups;
    }

    public function create($owner_code, $bank_code, $topup_amount, $topup_amount_unique_code)
    {
        // Get the owner
        $owner = Owner::where('owner_code', $owner_code)->first();
        if (!$owner) {
            return $this->sendError(2, 'Owner not found');
        }

        DB::beginTransaction();
        try {
            // Create the topup
            $topup = Topup::create([
                'topup_code' => generateFiledCode('TOPUP'),
                'owner_code' => $owner_code,
                'bank_code' => $bank_code,
                'topup_amount' => $topup_amount,
                'topup_amount_unique_code' => $topup_amount_unique_code,
                'topup_status' => 'PENDING',
            ]);

            DB::commit();

            return $topup;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to create topup', $e->getMessage());
        }
    }

    public function payment_success($topup_code)
    {
        // Get the topup
        $topup = Topup::where('topup_code', $topup_code)->first();
        if (!$topup) {
            return $this->sendError(2, 'Topup not found');
        }

        DB::beginTransaction();
        try {
            // Update the topup status
            $topup->update([
                'topup_status' => 'SUCCESS',
            ]);

            DB::commit();

            return $topup;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to update topup status', $e->getMessage());
        }
    }
}
