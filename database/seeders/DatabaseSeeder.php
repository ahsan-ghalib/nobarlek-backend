<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\HistoricalData\MatchDetailSeeder;
use Database\Seeders\HistoricalData\MatchPlayerLineupSeeder;
use Database\Seeders\HistoricalData\MatchPlayerStatisticSeeder as HistoricalMatchPlayerStatisticSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CategorySeeder::class,
            //    CoachSeeder::class,
            CompetitionSeeder::class,
            CompetitionPrioritySeeder::class,
            //    PlayerSeeder::class,
            RefereeSeeder::class,
            SeasonSeeder::class,
            StageSeeder::class,
            TeamSeeder::class,
            VenueSeeder::class,
            MatchDiarySeeder::class,
            FootballMatchSeeder::class,
            TeamSquadSeeder::class,
            IndonesianPresidentCupSeasonSeeder::class,
            //    PlayerTransferSeeder::class,
            //    HonorSeeder::class,
            //    PlayerHonorSeeder::class,
            //    CoachHonorSeeder::class,
            //    TeamHonorSeeder::class,
            //    TeamInjurySeeder::class,
            MatchDetailSeeder::class,
            MatchPlayerLineupSeeder::class,
            HistoricalMatchPlayerStatisticSeeder::class,
            TeamStandingSeeder::class,
            IndonesianPresidentCupStandingSeeder::class,
            // LanguageSeeder::class,
        ]);
    }
}
