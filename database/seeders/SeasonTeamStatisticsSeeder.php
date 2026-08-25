<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\DataScrapping\SeasonTeamStatisticsScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonTeamStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = Season::query()
            ->where('is_team_statistics_scrapped', '=', false)
            ->where('has_team_stats', '=', true);
        ScrapingCountryScope::restrictSeasonsQuery($query);

        $query->get()
            ->each(function ($season) {
                $result = (new SeasonTeamStatisticsScrapingService)->scrapSeasonTeamStatisticData(['uuid' => $season->season_id]);
                if (isset($result['error'])) {
                    $this->command?->error("Season {$season->season_id}: {$result['error']}");

                    return;
                }
                $season->update(['is_team_statistics_scrapped' => true]);
            });
    }
}
