<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('football_matches', function (Blueprint $table) {
            $table->index(['match_time', 'status_id']);
            $table->index('competition_id');
            $table->index('home_team_id');
            $table->index('away_team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('football_matches', function (Blueprint $table) {
            $table->dropIndex(['match_time', 'status_id']);
            $table->dropIndex('competition_id');
            $table->dropIndex('home_team_id');
            $table->dropIndex('away_team_id');
        });
    }
};
