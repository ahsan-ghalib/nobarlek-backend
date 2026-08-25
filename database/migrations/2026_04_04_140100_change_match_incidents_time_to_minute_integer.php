<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_EIGHT_COL = 'm_id_p_id_in_p_id_out_p_id_in_p_name_out_p_name_unique';

    private const UNIQUE_SIX_COL = 'm_i_type_time_m_id_p_id_in_p_id_out_p_id_unique';

    private const UNIQUE_FOUR_COL = 'match_incidents_type_time_match_id_player_id_unique';

    /**
     * TheSports API sends match minute (e.g. 3, 14), not a datetime.
     */
    public function up(): void
    {
        $this->dropMatchIncidentsTimeUniqueIndexes();

        Schema::table('match_incidents', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        Schema::table('match_incidents', function (Blueprint $table) {
            $table->unsignedSmallInteger('time')->nullable()->after('position');
        });

        DB::statement(
            'ALTER TABLE `match_incidents` ADD UNIQUE `'.self::UNIQUE_EIGHT_COL.'` '.
            '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64), `in_player_name`(96), `out_player_name`(96))'
        );
    }

    public function down(): void
    {
        Schema::table('match_incidents', function (Blueprint $table) {
            $table->dropUnique(self::UNIQUE_EIGHT_COL);
        });

        Schema::table('match_incidents', function (Blueprint $table) {
            $table->dropColumn('time');
        });

        Schema::table('match_incidents', function (Blueprint $table) {
            $table->timestamp('time')->nullable()->after('position');
        });

        DB::statement(
            'ALTER TABLE `match_incidents` ADD UNIQUE `'.self::UNIQUE_EIGHT_COL.'` '.
            '(`type`(64), `time`, `match_id`(64), `player_id`(64), `in_player_id`(64), `out_player_id`(64), `in_player_name`(96), `out_player_name`(96))'
        );
    }

    private function dropMatchIncidentsTimeUniqueIndexes(): void
    {
        foreach ([self::UNIQUE_EIGHT_COL, self::UNIQUE_SIX_COL, self::UNIQUE_FOUR_COL] as $indexName) {
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
        }
    }
};
