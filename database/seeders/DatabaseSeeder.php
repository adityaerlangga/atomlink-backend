<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\OwnersSeeder;
use Database\Seeders\VariabelUnitsSeeder;
use Database\Seeders\VariabelCitiesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(VariabelCitiesSeeder::class);
        $this->call(VariabelUnitsSeeder::class);
        $this->call(VariabelBanksSeeder::class);

        $this->call(OwnersSeeder::class);
        $this->call(OutletsSeeder::class);
    }
}
