<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns `match_player_statistics` with sports API `football/match/player_stats/list` fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_player_statistics', function (Blueprint $table) {
            $table->integer('aerial_won')->default(0)->after('poss_losts');
            $table->integer('aerial_lost')->default(0)->after('aerial_won');
            $table->integer('duel_won')->default(0)->after('aerial_lost');
            $table->integer('duel_lost')->default(0)->after('duel_won');
        });
    }

    public function down(): void
    {
        Schema::table('match_player_statistics', function (Blueprint $table) {
            $table->dropColumn(['aerial_won', 'aerial_lost', 'duel_won', 'duel_lost']);
        });
    }
};
