<?php

namespace App\Http\Repositories;

use App\Models\Owner;
use App\Models\Coin;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;

class CoinRepository extends ApiController
{
    /**
     * Update the balance of an OWNER.
     *
     * @param string $owner_code Kode OWNER.
     * @param string $type Jenis transaksi ('INCOME' atau 'EXPENSE').
     * @param string $categories (TOPUP, TRANSACTION, WHATSAPP_TRANSACTION, MASS_INVOICE).
     * @param float $amount Jumlah yang ditambahkan atau dikurangi.
     * @param string $description Deskripsi transaksi.
     *
     * @return bool True jika berhasil, sebaliknya false.
     */
    public function updateBalance($owner_code, $type, $categories, $amount, $description)
    {
        // Get the owner
        $owner = Owner::where('owner_code', $owner_code)->first();

        // Calculate the new balance
        $new_balance = $type === 'INCOME' ? $owner->owner_balance + $amount : $owner->owner_balance - $amount;

        DB::beginTransaction();
        try {
            // Update the owner's balance
            $owner->update([
                'owner_balance' => $new_balance
            ]);

            // Log the transaction
            $this->logCoin($owner_code, $type, $categories, $description, $amount, $new_balance);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to update balance', $e->getMessage());
        }
    }

    /**
     * Log the coin transaction.
     *
     * @param string $owner_code Kode OWNER.
     * @param string $type Jenis transaksi ('INCOME' atau 'EXPENSE').
     * @param string $categories Kategori transaksi (TOPUP, TRANSACTION, WHATSAPP_TRANSACTION, MASS_INVOICE).
     * @param string $description Deskripsi transaksi.
     * @param float $amount Jumlah yang ditambahkan atau dikurangi.
     * @param float $new_balance Saldo baru setelah transaksi.
     *
     * @return bool True jika berhasil, sebaliknya false.
     */
    public function logCoin($owner_code, $type, $categories, $description, $amount, $new_balance)
    {
        DB::beginTransaction();
        try {
            Coin::create([
                'coin_code' => generateFiledCode('LOG_COIN'),
                'owner_code' => $owner_code,
                'coin_type' => $type,
                'coin_category' => $categories,
                'coin_amount' => $amount,
                'coin_new_balance' => $new_balance,
                'coin_description' => $description,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(1, 'Failed to log transaction', $e->getMessage());
        }
    }
}
