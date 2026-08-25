<?php

namespace App\Console\Commands;

use App\Services\MatchDataFolderImportService;
use Illuminate\Console\Command;

class ImportMatchDataFolderCommand extends Command
{
    protected $signature = 'match-data:import
                            {--type= : Only import match_lineup, match_statistics, or player_statistics}';

    protected $description = 'Import JSON match snapshots from public/match-data into the database';

    public function handle(MatchDataFolderImportService $service): int
    {
        ini_set('memory_limit', '-1');

        $type = $this->option('type');
        if ($type !== null && ! in_array($type, [
            MatchDataFolderImportService::TYPE_LINEUP,
            MatchDataFolderImportService::TYPE_MATCH_STATISTICS,
            MatchDataFolderImportService::TYPE_PLAYER_STATISTICS,
        ], true)) {
            $this->error('Invalid --type. Use match_lineup, match_statistics, or player_statistics.');

            return self::FAILURE;
        }

        $bar = null;
        $stats = $service->run($type, function () use (&$bar) {
            if ($bar === null) {
                return;
            }
            $bar->advance();
        });

        if (! empty($stats['missing_directory'])) {
            $this->error('Directory not found: '.public_path('match-data'));

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $stats['processed']],
                ['Skipped (out of scope / no match row)', $stats['skipped']],
                ['Errors', $stats['errors']],
                ['Lineups', $stats['by_type'][MatchDataFolderImportService::TYPE_LINEUP] ?? 0],
                ['Match statistics', $stats['by_type'][MatchDataFolderImportService::TYPE_MATCH_STATISTICS] ?? 0],
                ['Player statistics', $stats['by_type'][MatchDataFolderImportService::TYPE_PLAYER_STATISTICS] ?? 0],
            ],
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
