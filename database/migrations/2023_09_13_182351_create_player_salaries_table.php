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
        Schema::create('player_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('player_id');
            $table->string('team_id');
            $table->string('season');
            $table->string('weekly');
            $table->string('annual');
            $table->string('mode');
            $table->bigInteger('contract_until');
            $table->timestamps();

            $table->index(['player_id', 'team_id', 'season']);
            $table->unique(['player_id', 'team_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_salaries');
    }
};
