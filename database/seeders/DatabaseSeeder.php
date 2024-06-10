<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\OwnersSeeder;
use Database\Seeders\ServicesSeeder;
use Database\Seeders\VariabelUnitsSeeder;
use Database\Seeders\VariabelCitiesSeeder;
use Database\Seeders\VariabelServiceCategoriesSeeder;

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
        $this->call(VariabelServiceCategoriesSeeder::class);


        $this->call(OwnersSeeder::class);
        $this->call(OutletsSeeder::class);
        $this->call(WorkshopsSeeder::class);
        $this->call(ServicesSeeder::class);
    }
}
