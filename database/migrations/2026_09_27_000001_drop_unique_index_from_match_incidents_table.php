<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_EIGHT_COL = 'm_id_p_id_in_p_id_out_p_id_in_p_name_out_p_name_unique';

    /**
     * Incidents are now replaced per match (MatchIncidents::replaceForMatch), so the
     * unique key is no longer needed and would reject legitimate rows such as two
     * goals by unnamed players in the same minute.
     */
    public function up(): void
    {
        if ($this->indexExists(self::UNIQUE_EIGHT_COL)) {
            Schema::table('match_incidents', function (Blueprint $table) {
                $table->dropUnique(self::UNIQUE_EIGHT_COL);
            });
        }
    }

    public function down(): void
    {
        if (! $this->indexExists(self::UNIQUE_EIGHT_COL)) {
            DB::statement(
                'ALTER TABLE `match_incidents` ADD UNIQUE `'.self::UNIQUE_EIGHT_COL.'` '.
                '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64), `in_player_name`(96), `out_player_name`(96))'
            );
        }
    }

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'match_incidents')
            ->where('index_name', $indexName)
            ->exists();
    }
};
