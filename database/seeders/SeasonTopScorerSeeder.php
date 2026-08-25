<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\DataScrapping\SeasonPlayerTopScorerScrapingService;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonTopScorerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $query = Season::query()->select(['season_id']);
        ScrapingCountryScope::restrictSeasonsQuery($query);

        $query->get()
            ->map(function ($season) {
                (new SeasonPlayerTopScorerScrapingService())->scrapSeasonTopScorerData(['uuid' => $season->season_id]);
                sleep(2);
            });
    }
}
