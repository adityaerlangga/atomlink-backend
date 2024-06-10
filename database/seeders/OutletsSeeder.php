<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class OutletsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create();

        $i = 1;
        foreach(range(1, 100) as $index) {
            $outlet_code = $i <= 5 ? 'OUTLET-000' . $i : generateFiledCode('OUTLET');

            Outlet::create([
                'outlet_code' => $outlet_code,
                'owner_code' => 'OWNER-000' . mt_rand(1,5),
                'outlet_name' => $faker->name,
                'outlet_phone_number' => '0812' . $faker->randomNumber(8),
                'city_code' => 'CITY-000' . mt_rand(1,5),
                'outlet_address' => $faker->address(),
                'outlet_logo' => null,
                'workshop_code' => 'WORKSHOP-000' . mt_rand(1,5),
            ]);

            $i++;
        }

    }
}
