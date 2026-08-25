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
        Schema::create('team_squads', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->index();
            $table->string('player_id')->index();
            $table->string('position');
            $table->string('shirt_number');

            $table->unique(['team_id', 'player_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_squads');
    }
};
