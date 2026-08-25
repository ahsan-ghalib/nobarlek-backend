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
        Schema::create('match_team_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->index();
            $table->string('team_id')->index();
            $table->integer('goals')->default(0);
            $table->integer('penalty')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('red_cards')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('shots')->default(0);
            $table->integer('shots_on_target')->default(0);
            $table->integer('dribble')->default(0);
            $table->integer('dribble_succ')->default(0);
            $table->integer('clearances')->default(0);
            $table->integer('blocked_shots')->default(0);
            $table->integer('interceptions')->default(0);
            $table->integer('tackles')->default(0);
            $table->integer('passes')->default(0);
            $table->integer('passes_accuracy')->default(0);
            $table->integer('key_passes')->default(0);
            $table->integer('crosses')->default(0);
            $table->integer('crosses_accuracy')->default(0);
            $table->integer('long_balls')->default(0);
            $table->integer('long_balls_accuracy')->default(0);
            $table->integer('duels')->default(0);
            $table->integer('duels_won')->default(0);
            $table->integer('fouls')->default(0);
            $table->integer('was_fouled')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('offsides')->default(0);
            $table->integer('yellow2red_cards')->default(0);
            $table->integer('corner_kicks')->default(0);
            $table->integer('ball_possession')->default(0);
            $table->integer('freekicks')->default(0);
            $table->integer('freekick_goals')->default(0);
            $table->integer('hit_woodwork')->default(0);
            $table->integer('fastbreaks')->default(0);
            $table->integer('fastbreak_shots')->default(0);
            $table->integer('fastbreak_goals')->default(0);
            $table->integer('poss_losts')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_team_statistics');
    }
};
