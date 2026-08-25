<?php

namespace Database\Seeders;

use App\Services\DataScrapping\SeasonScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new SeasonScrapingService())->scrapSeasonData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
