<?php

namespace Database\Seeders;

use App\Services\DataScrapping\CompetitionScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new CompetitionScrapingService())->scrapCompetitionData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
