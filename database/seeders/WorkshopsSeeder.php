<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Workshop;
use App\Models\WorkshopProductionStep;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class WorkshopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create();

        $i = 1;
        foreach(range(1, 5) as $index) {
            $workshop_code = $i <= 5 ? 'WORKSHOP-000' . $i : generateFiledCode('WORKSHOP');

            Workshop::create([
                'workshop_code' => $workshop_code,
                'owner_code' => 'OWNER-000' . mt_rand(1,5),
                'workshop_name' => $faker->name,
                'workshop_phone_number' => '0812' . $faker->randomNumber(8),
                'city_code' => 'CITY-000' . mt_rand(1,5),
                'workshop_address' => $faker->address(),
                'is_hide' => false,
                'is_deleted' => false,
            ]);

            WorkshopProductionStep::create([
                'workshop_production_step_code' => $workshop_code,
                'workshop_code' => $workshop_code,
                'workshop_labeling' => false,
                'workshop_sorting' => false,
                'workshop_cleaning' => false,
                'workshop_spotting' => false,
                'workshop_detailing' => false,
                'workshop_washing' => false,
                'workshop_drying' => false,
                'workshop_ironing' => false,
                'workshop_extra_ironing' => false,
                'workshop_folding' => false,
                'workshop_packaging' => true,
            ]);
            $i++;
        }
    }
}
