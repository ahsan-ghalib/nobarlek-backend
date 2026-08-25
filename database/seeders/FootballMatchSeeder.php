<?php

namespace Database\Seeders;

use App\Services\DataScrapping\MatchScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FootballMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new MatchScrapingService())->scrapMatchData(['page' => $pageNo]);
            $pageNo++;
        } while ($query['total'] !== 0);
    }
}
