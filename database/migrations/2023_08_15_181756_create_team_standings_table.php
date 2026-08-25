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
        Schema::create('team_standings', function (Blueprint $table) {
            $table->id();
            $table->string('season_id')->index();
            $table->string('standing_id')->index();
            $table->string('conference')->nullable();
            $table->string('group');
            $table->string('stage_id')->index();
            $table->string('team_id')->index();
            $table->string('promotion_id')->nullable();
            $table->integer('points');
            $table->integer('position');
            $table->integer('deduct_points');
            $table->string('note')->nullable();
            $table->integer('total');
            $table->integer('won');
            $table->integer('draw');
            $table->integer('loss');
            $table->integer('goals');
            $table->integer('goals_against');
            $table->integer('goal_diff');
            $table->integer('home_points');
            $table->integer('home_position');
            $table->integer('home_total');
            $table->integer('home_won');
            $table->integer('home_draw');
            $table->integer('home_loss');
            $table->integer('home_goals');
            $table->integer('home_goals_against');
            $table->integer('home_goal_diff');
            $table->integer('away_points');
            $table->integer('away_position');
            $table->integer('away_total');
            $table->integer('away_won');
            $table->integer('away_draw');
            $table->integer('away_loss');
            $table->integer('away_goals');
            $table->integer('away_goals_against');
            $table->integer('away_goal_diff');

            $table->unique(['season_id', 'standing_id','team_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_standings');
    }
};
