<?php

namespace Database\Seeders;

use App\Services\DataScrapping\VenueScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new VenueScrapingService())->scrapVenueData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
