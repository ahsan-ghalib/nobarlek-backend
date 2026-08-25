<?php

namespace Database\Seeders\HistoricalData;

use App\Services\MatchDataFolderImportService;
use Illuminate\Database\Seeder;

/**
 * Import historical match snapshots from `public/match-data/`.
 *
 * Folder layout:
 *   public/match-data/{Competition Name}/{Season}/match_lineup/{match_id}.txt
 *   public/match-data/{Competition Name}/{Season}/match_statistics/{match_id}.txt
 *   public/match-data/{Competition Name}/{Season}/player_statistics/{match_id}.txt
 *
 * Prerequisite: matches must already exist (`MatchDetailSeeder` or `MatchDiarySeeder`).
 *
 * Usage:
 *   php artisan db:seed --class=Database\\Seeders\\HistoricalData\\MatchDataFolderSeeder
 */
class MatchDataFolderSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '-1');

        $service = new MatchDataFolderImportService;
        $progressEvery = 250;
        $lastReport = 0;

        $stats = $service->run(null, function (string $matchId, string $type, array $stats) use (&$lastReport, $progressEvery) {
            if ($stats['processed'] - $lastReport < $progressEvery) {
                return;
            }

            $lastReport = $stats['processed'];
            $this->command?->info(sprintf(
                'Imported %d files (skipped %d, errors %d) — latest %s [%s]',
                $stats['processed'],
                $stats['skipped'],
                $stats['errors'],
                $matchId,
                $type,
            ));
        });

        if (! empty($stats['missing_directory'])) {
            $this->command?->error('Directory not found: '.public_path('match-data'));

            return;
        }

        $this->command?->info(sprintf(
            'Match data import finished. processed=%d skipped=%d errors=%d lineup=%d statistics=%d player_stats=%d',
            $stats['processed'],
            $stats['skipped'],
            $stats['errors'],
            $stats['by_type'][MatchDataFolderImportService::TYPE_LINEUP] ?? 0,
            $stats['by_type'][MatchDataFolderImportService::TYPE_MATCH_STATISTICS] ?? 0,
            $stats['by_type'][MatchDataFolderImportService::TYPE_PLAYER_STATISTICS] ?? 0,
        ));
    }
}
