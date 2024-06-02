<?php

namespace Database\Seeders;

use App\Models\VariableBanks;
use Illuminate\Database\Seeder;

class VariabelBanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                "bank_code" => "bni",
                "bank_name" => "BNI - Bank Negara Indonesia",
                "bank_logo" => "https://www.bni.co.id/Portals/1/bni-logo-id.png",
                "bank_account_number" => "1234567890",
                "bank_account_holder" => "ALFA SETIAWAN"
            ],
            [
                "bank_code" => "bca",
                "bank_name" => "BCA - Bank Central Asia",
                "bank_logo" => "https://www.bca.co.id/assets/images/logo-bca.png",
                "bank_account_number" => "1234567890",
                "bank_account_holder" => "ALFA SETIAWAN"
            ]
        ];

        foreach ($items as $item) {
            VariableBanks::create([
                'bank_code' => $item['bank_code'],
                'bank_name' => $item['bank_name'],
                'bank_logo' => $item['bank_logo'],
                'bank_account_number' => $item['bank_account_number'],
                'bank_account_holder' => $item['bank_account_holder']
            ]);
        }

    }
}
