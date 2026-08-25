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
        Schema::create('player_honors', function (Blueprint $table) {
            $table->id();
            $table->string('player_id');
            $table->string('honor_id');
            $table->string('season');
            $table->string('competition_id');
            $table->string('season_id');
            $table->timestamps();

            $table->index(['player_id', 'honor_id', 'season_id']);
            $table->unique(['player_id', 'honor_id', 'season_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_honors');
    }
};
