<?php

namespace Database\Seeders;

use App\Services\DataScrapping\CountryScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new CountryScrapingService())->scrapCountryData();
    }
}
