<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns `match_team_statistics` with sports API team_stats fields.
 * Original migration stopped at `poss_losts`; API also sends aerial/ground duel breakdowns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_team_statistics', function (Blueprint $table) {
            if (! Schema::hasColumn('match_team_statistics', 'aerial_won')) {
                $table->integer('aerial_won')->default(0)->after('poss_losts');
            }
            if (! Schema::hasColumn('match_team_statistics', 'aerial_lost')) {
                $table->integer('aerial_lost')->default(0)->after('aerial_won');
            }
            if (! Schema::hasColumn('match_team_statistics', 'duel_won')) {
                $table->integer('duel_won')->default(0)->after('aerial_lost');
            }
            if (! Schema::hasColumn('match_team_statistics', 'duel_lost')) {
                $table->integer('duel_lost')->default(0)->after('duel_won');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_team_statistics', function (Blueprint $table) {
            $table->dropColumn(['aerial_won', 'aerial_lost', 'duel_won', 'duel_lost']);
        });
    }
};
