<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Season;
use Illuminate\Database\Seeder;
use RuntimeException;

class IndonesianPresidentCupSeasonSeeder extends Seeder
{
    private const COMPETITION_ID = 'j1l4rjnhkz5m7vx';

    private const CURRENT_SEASON_ID = '3glrw7hww18qdyj';

    private const SEASONS = [
        '3glrw7hww18qdyj' => 2026,
        'gpxwrxlhdj5ryk0' => 2025,
        'v2y8m4zhkd6ql07' => 2024,
        'yl5ergph690r8k0' => 2022,
        'kjw2r09h36nrz84' => 2019,
    ];

    /**
     * Seed every Indonesian President Cup season.
     */
    public function run(): void
    {
        $competition = Competition::query()
            ->where('competition_id', self::COMPETITION_ID)
            ->first();

        if ($competition === null) {
            throw new RuntimeException(
                'Indonesian President Cup is missing from the competitions table. Run CompetitionSeeder first.'
            );
        }

        foreach (self::SEASONS as $seasonId => $year) {
            Season::query()->updateOrCreate(
                ['season_id' => $seasonId],
                [
                    'competition_id' => self::COMPETITION_ID,
                    'year' => $year,
                    'has_player_stats' => false,
                    'has_team_stats' => false,
                    'has_table' => false,
                    'is_current' => $seasonId === self::CURRENT_SEASON_ID,
                    'start_time' => null,
                    'end_time' => null,
                ]
            );
        }

        $competition->update([
            'cur_season_id' => self::CURRENT_SEASON_ID,
        ]);

        $this->command?->info(
            count(self::SEASONS).' Indonesian President Cup seasons seeded.'
        );
    }
}
