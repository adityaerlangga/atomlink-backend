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

        $i = 1;
        foreach ($units as $item) {
            $service_code = $i <= 5 ? 'UNIT-000' . $i : generateFiledCode('UNIT');

            \App\Models\VariableUnits::create([
                'unit_code' => $service_code,
                'unit_name' => $item
            ]);

            $i++;
        }

    }
}
