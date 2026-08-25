<?php

namespace Database\Seeders;


use App\Services\DataScrapping\TeamSquadScrapingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSquadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageNo = 1;
        do {
            $query = (new TeamSquadScrapingService())->scrapTeamSquadData(['page' => $pageNo]);
            $pageNo++;
        } while ($query['total'] !== 0);
    }
}
