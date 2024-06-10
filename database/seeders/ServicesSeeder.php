<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create();

        $i = 1;
        foreach(range(1, 5) as $index) {
            $service_code = $i <= 5 ? 'SERVICE-000' . $i : generateFiledCode('SERVICE');

            Service::create([
                'service_code' => $service_code,
                'outlet_code' => 'OUTLET-000' . mt_rand(1,5),
                'service_name' => $faker->name,
                'service_price' => mt_rand(8000, 100000),
                'unit_code' => 'UNIT-000' . mt_rand(1,5),
                'service_duration_days' => mt_rand(1, 5),
                'service_duration_hours' => mt_rand(0, 23),
            ]);

            $i++;
        }

    }
}
