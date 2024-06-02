<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VariabelUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            "KG", "M2", "BARANG", "PCS", "LEBAR", "STEL", "M", "PAKET", "UNIT", "DUDUKAN", "PASANG", "CM2",
            "KOIN", "LOAD", "HELAI", "MILI", "BIJI", "CM"
            ];

        foreach ($units as $item) {
            \App\Models\VariableUnits::create([
                'unit_code' => generateFiledCode('UNIT'),
                'unit_name' => $item
            ]);
        }

    }
}
