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
        Schema::create('season_player_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('season_id');
            $table->string('player_id');
            $table->string('team_id');
            $table->integer('matches');
            $table->integer('court');
            $table->integer('first');
            $table->integer('goals');
            $table->integer('penalty');
            $table->integer('assists');
            $table->integer('minutes_played');
            $table->integer('red_cards');
            $table->integer('yellow_cards');
            $table->integer('shots');
            $table->integer('shots_on_target');
            $table->integer('dribble');
            $table->integer('dribble_succ');
            $table->integer('clearances');
            $table->integer('blocked_shots');
            $table->integer('interceptions');
            $table->integer('tackles');
            $table->integer('passes');
            $table->integer('passes_accuracy');
            $table->integer('key_passes');
            $table->integer('crosses');
            $table->integer('crosses_accuracy');
            $table->integer('long_balls');
            $table->integer('long_balls_accuracy');
            $table->integer('duels');
            $table->integer('duels_won');
            $table->integer('dispossessed');
            $table->integer('fouls');
            $table->integer('was_fouled');
            $table->integer('offsides');
            $table->integer('yellow2red_cards');
            $table->integer('saves');
            $table->integer('punches');
            $table->integer('runs_out');
            $table->integer('runs_out_succ');
            $table->integer('good_high_claim');
            $table->integer('rating');
            $table->integer('freekicks');
            $table->integer('freekick_goals');
            $table->integer('hit_woodwork');
            $table->integer('fastbreaks');
            $table->integer('fastbreak_shots');
            $table->integer('fastbreak_goals');
            $table->integer('poss_losts');
            $table->timestamps();

            $table->index(['season_id', 'player_id','team_id']);
            $table->unique(['season_id', 'player_id','team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_player_statistics');
    }
};
