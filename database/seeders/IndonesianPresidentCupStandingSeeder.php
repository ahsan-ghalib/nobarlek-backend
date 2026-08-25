<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Services\DataScrapping\TeamStandingScrapingService;
use Illuminate\Database\Seeder;

class IndonesianPresidentCupStandingSeeder extends Seeder
{
    private const COMPETITION_ID = 'j1l4rjnhkz5m7vx';

    /**
     * Fetch the standings for every seeded Indonesian President Cup season.
     */
    public function run(): void
    {
        $seasons = Season::query()
            ->where('competition_id', self::COMPETITION_ID)
            ->orderByDesc('year')
            ->get();

        if ($seasons->isEmpty()) {
            $this->command?->warn(
                'No Indonesian President Cup seasons found. Run IndonesianPresidentCupSeasonSeeder first.'
            );

            return;
        }

        $scraper = new TeamStandingScrapingService;

        foreach ($seasons as $season) {
            $scraper->scrapHistoricalTeamStandingData([
                'uuid' => $season->season_id,
            ]);

            $hasStandings = $season->teamStandings()->exists();

            $season->update([
                'has_table' => $hasStandings,
            ]);

            $message = $hasStandings ? 'imported' : 'not available from the API';
            $this->command?->line("President Cup {$season->year} standings: {$message}.");
        }
    }
}
