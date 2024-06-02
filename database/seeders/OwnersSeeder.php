<?php

namespace Database\Seeders;

use App\Models\Owner;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class OwnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create();

        $i = 1;
        foreach(range(1, 10) as $index) {
            $owner_code = $i <= 5 ? 'OWNER-000' . $i : generateFiledCode('OWNER');

            Owner::create([
                'owner_code' => $owner_code,
                'owner_name' => $faker->name,
                'city_code' => 'CITY-000' . mt_rand(1,5),
                'owner_whatsapp_number' => '0812' . $faker->randomNumber(8),
                'owner_email' => $faker->email,
            ]);

            $i++;
        }

    }
}
