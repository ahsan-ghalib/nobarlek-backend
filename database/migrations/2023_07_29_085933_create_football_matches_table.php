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
        Schema::create('football_matches', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->unique()->index();
            $table->string('season_id')->nullable();
            $table->string('competition_id');
            $table->string('home_team_id');
            $table->string('away_team_id');
            $table->string('status_id');
            $table->string('match_time');
            $table->string('venue_id')->nullable();
            $table->string('referee_id');
            $table->string('neutral');
            $table->string('note')->nullable();
            $table->text('home_scores');
            $table->text('away_scores');
            $table->string('home_position')->nullable();
            $table->string('away_position')->nullable();
            $table->boolean('mlive')->default(false);
            $table->boolean('lineup')->default(false);
            $table->string('stage_id');
            $table->string('group_num');
            $table->string('round_num');
            $table->string('related_id')->nullable();
            $table->text('agg_score');
            $table->text('environment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('football_matches');
    }
};
