<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\DataScrapping\TeamStandingScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamStandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = Season::query()
            ->where('year', '>=', 2019)
            ->where('has_table', true);
        ScrapingCountryScope::restrictSeasonsQuery($query);

        $query->get()
            ->map(function ($season) {
                (new TeamStandingScrapingService())->scrapHistoricalTeamStandingData(['uuid' => $season->season_id]);
            });
    }
}
