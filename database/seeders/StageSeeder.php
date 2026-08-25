<?php

namespace Database\Seeders;

use App\Services\DataScrapping\StageScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new StageScrapingService())->scrapStageData(['page' => $pageNo]);
            $pageNo++;
        } while ($query['total'] !== 0);
    }
}
