<?php

namespace Database\Seeders;

use App\Services\DataScrapping\RefereeScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RefereeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new RefereeScrapingService())->scrapRefereeData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
