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
        Schema::create('season_top_scorers', function (Blueprint $table) {
            $table->id();
            $table->string('season_id');
            $table->string('player_id');
            $table->string('team_id');
            $table->integer('goals');
            $table->integer('position');
            $table->integer('penalty');
            $table->integer('assists');
            $table->integer('minutes_played');
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
        Schema::dropIfExists('season_top_scorers');
    }
};
