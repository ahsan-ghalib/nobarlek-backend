<?php

namespace Database\Seeders;

use App\Services\DataScrapping\CoachScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new CoachScrapingService())->scrapCoachData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
