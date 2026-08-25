<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->after('end_time', function (Blueprint $table) {
                $table->boolean('is_player_statistics_scrapped')->default(false);
                $table->boolean('is_team_statistics_scrapped')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn([
                'is_player_statistics_scrapped',
                'is_team_statistics_scrapped'
            ]);
        });
    }
};
