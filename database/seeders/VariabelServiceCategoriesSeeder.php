<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VariabelServiceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = ['Kiloan', 'Satuan', 'Luas', 'Lainnya'];

        $i = 1;
        foreach ($items as $item) {
            \App\Models\VariableServiceCategory::create([
                'service_category_code' => "SERVICE_CATEGORY-000" . $i,
                'service_category_name' => $item
            ]);
            $i++;
        }
    }
}
