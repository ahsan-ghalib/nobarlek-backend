<?php

namespace Database\Seeders;

use App\Services\DataScrapping\TeamInjurycrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamInjurySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new TeamInjurycrapingService())->scrapTeamInjuryData(['page'  => $pageNo]);
            $pageNo++;
        } while($query['total'] !== 0);
    }
}
