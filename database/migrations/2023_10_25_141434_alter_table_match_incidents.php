<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
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
            return;
        }

        // Prefixed columns: five utf8mb4 strings exceed the 3072-byte InnoDB limit if indexed at full width.
        DB::statement(
            'ALTER TABLE `match_incidents` ADD UNIQUE `m_i_type_time_m_id_p_id_in_p_id_out_p_id_unique` '.
            '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64))'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_incidents', function (Blueprint $table) {
            $table->dropUnique('m_i_type_time_m_id_p_id_in_p_id_out_p_id_unique');
//            $table->unique(['type', 'time', 'match_id', 'player_id']);
        });
    }
};
