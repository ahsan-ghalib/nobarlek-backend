<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const COMPETITION_TIME_INDEX = 'football_matches_competition_id_match_time_index';

    /**
     * `match_time` holds unix seconds but was a varchar, so every date-window query
     * compared it through a cast and scanned the whole index. As an integer column
     * the existing (match_time, status_id) index can serve range filters, and the new
     * (competition_id, match_time) index serves per-competition counts and lists.
     */
    public function up(): void
    {
        DB::statement("UPDATE `football_matches` SET `match_time` = '0' WHERE `match_time` IS NULL OR `match_time` NOT REGEXP '^[0-9]+$'");
        DB::statement('ALTER TABLE `football_matches` MODIFY `match_time` INT UNSIGNED NOT NULL DEFAULT 0');

        if (! $this->indexExists(self::COMPETITION_TIME_INDEX)) {
            DB::statement('ALTER TABLE `football_matches` ADD INDEX `'.self::COMPETITION_TIME_INDEX.'` (`competition_id`(64), `match_time`)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists(self::COMPETITION_TIME_INDEX)) {
            DB::statement('ALTER TABLE `football_matches` DROP INDEX `'.self::COMPETITION_TIME_INDEX.'`');
        }

        DB::statement('ALTER TABLE `football_matches` MODIFY `match_time` VARCHAR(255) NOT NULL');
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'football_matches')
            ->where('index_name', $indexName)
            ->exists();
    }
};
