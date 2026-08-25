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
        Schema::create('match_information', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->index();
            $table->string('competition_id')->index();
            $table->integer('match_status');
            $table->integer('match_time');
            $table->integer('kick_off');
            $table->string('home_team_id');
            $table->string('home_league_ranking');
            $table->integer('home_team_score');
            $table->integer('home_team_halftime_score');
            $table->integer('home_team_red_cards');
            $table->integer('home_team_yellow_cards');
            $table->integer('home_team_corners');
            $table->integer('home_team_overtime_score');
            $table->integer('home_team_penalty_shootout_score');
            $table->string('away_team_id');
            $table->string('away_league_ranking');
            $table->integer('away_team_score');
            $table->integer('away_team_halftime_score');
            $table->integer('away_team_red_cards');
            $table->integer('away_team_yellow_cards');
            $table->integer('away_team_corners');
            $table->integer('away_team_overtime_score');
            $table->integer('away_team_penalty_shootout_score');
            $table->string('asian_plate_home');
            $table->string('european_plate');
            $table->string('size_ball_plate');
            $table->string('corner_plate');
            $table->string('match_description');
            $table->integer('is_it_neutral');
            $table->integer('match_round');
            $table->string('season_id')->index();
            $table->string('season_year');
            $table->timestamps();

            $table->unique(['match_id', 'competition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_information');
    }
};
