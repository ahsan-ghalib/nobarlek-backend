<?php

namespace Database\Seeders;

use App\Services\DataScrapping\TeamScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new TeamScrapingService())->scrapTeamData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
