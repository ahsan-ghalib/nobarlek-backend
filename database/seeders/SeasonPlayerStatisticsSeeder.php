<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\DataScrapping\SeasonPlayerStatisticsScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonPlayerStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = Season::query()
            ->where('is_player_statistics_scrapped', '=', false)
            ->where('has_player_stats', '=', true);
        ScrapingCountryScope::restrictSeasonsQuery($query);

        $query->get()
            ->each(function ($season) {
                $result = (new SeasonPlayerStatisticsScrapingService)->scrapSeasonPlayerStatisticData(['uuid' => $season->season_id]);
                if (isset($result['error'])) {
                    $this->command?->error("Season {$season->season_id}: {$result['error']}");

                    return;
                }
                $season->update(['is_player_statistics_scrapped' => true]);
            });
    }
}
