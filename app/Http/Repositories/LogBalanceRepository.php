<?php

namespace App\Http\Repositories;

use App\Models\Owner;
use App\Models\LogBalance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class LogBalanceRepository extends ApiController
{
    /**
     * Menambah saldo OWNER.
     *
     * @param string $owner_code Kode OWNER.
     * @param string $categories (TOPUP, TRANSACTION, WHATSAPP_TRANSACTION, MASS_INVOICE).
     * @param float $amount Jumlah yang ditambahkan.
     * @param string $description Deskripsi transaksi.
     *
     * @return bool True jika berhasil, sebaliknya false.
     */
    public function IncomeBalance($owner_code, $categories, $amount, $description)
    {
        $type = 'INCOME';

        // INCOME
        $owner = Owner::where('owner_code', $owner_code)->first();
        $new_balance = $owner->owner_balance + $amount;

        DB::beginTransaction();
        try {
            $owner->update([
                'owner_balance' => $new_balance
            ]);

            DB::commit();

            $this->LogBalance($owner_code, $type, $categories, $description, $amount, $new_balance);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to top up balance', $e->getMessage());
        }
    }

    /**
     * Mengurangi saldo OWNER.
     *
     * @param string $owner_code Kode OWNER.
     * @param string $categories Kategori transaksi (TOPUP, TRANSACTION, WHATSAPP_TRANSACTION, MASS_INVOICE).
     * @param float $amount Jumlah yang dikurangi.
     * @param string $description Deskripsi transaksi.
     *
     * @return bool True jika berhasil, sebaliknya false.
     */
    public function ExpenseBalance($owner_code, $categories, $amount, $description)
    {
        $type = 'EXPENSE';

        // EXPENSE
        $owner = Owner::where('owner_code', $owner_code)->first();
        $new_balance = $owner->owner_balance - $amount;

        DB::beginTransaction();
        try {
            $owner->update([
                'owner_balance' => $new_balance
            ]);

            DB::commit();

            $this->LogBalance($owner_code, $type, $categories, $description, $amount, $new_balance);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to reduce balance', $e->getMessage());
        }
    }



    public function LogBalance($owner_code, $type, $categories, $description, $amount, $new_balance)
    {
        DB::beginTransaction();
        try {
            $log_balance = LogBalance::create([
                'log_balance_code' => generateFiledCode('LOG_BALANCE'),
                'owner_code' => $owner_code,
                'log_balance_type' => $type,
                'log_balance_category' => $categories,
                'log_balance_description' => $description,
                'log_balance_amount' => $amount,
                'log_balance_new_balance' => $new_balance,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to top up balance', $e->getMessage());
        }
    }
}
