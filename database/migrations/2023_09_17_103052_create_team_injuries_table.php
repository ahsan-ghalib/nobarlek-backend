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
        Schema::create('team_injuries', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('player_id')->index();
            $table->string('competition_id')->index();
            $table->string('injury_id')->index();
            $table->string('team_id')->index();
            $table->string('reason');
            $table->integer('start_time');
            $table->integer('end_time');
            $table->integer('missed_matches');
            $table->timestamps();

            $table->unique(['player_id', 'competition_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_injuries');
    }
};
