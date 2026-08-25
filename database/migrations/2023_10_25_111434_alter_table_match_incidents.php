<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The named unique may not exist yet: 2023_10_25_141434 (creates it) runs later
     * in the migration order than this file, so use a conditional drop.
     */
    public function up(): void
    {
        $indexName = 'm_i_type_time_m_id_p_id_in_p_id_out_p_id_unique';
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'match_incidents')
            ->where('index_name', $indexName)
            ->exists();

        if ($exists) {
            Schema::table('match_incidents', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        // Full-width unique on eight utf8mb4 strings exceeds InnoDB's 3072-byte index limit; use prefixes.
        DB::statement(
            'ALTER TABLE `match_incidents` ADD UNIQUE `m_id_p_id_in_p_id_out_p_id_in_p_name_out_p_name_unique` '.
            '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64), `in_player_name`(96), `out_player_name`(96))'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_incidents', function (Blueprint $table) {
            $table->dropUnique('m_id_p_id_in_p_id_out_p_id_in_p_name_out_p_name_unique');
        });

        DB::statement(
            'ALTER TABLE `match_incidents` ADD UNIQUE `m_i_type_time_m_id_p_id_in_p_id_out_p_id_unique` '.
            '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64))'
        );
    }
};
