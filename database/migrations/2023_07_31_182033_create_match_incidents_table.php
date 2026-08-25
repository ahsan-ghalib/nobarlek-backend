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
        Schema::create('match_incidents', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->integer('position')->default(0);
            $table->timestamp('time');
            $table->string('match_id')->index();
            $table->string('player_id')->index();
            $table->string('player_name');
            $table->string('assist1_id')->nullable();
            $table->string('assist1_name')->nullable();
            $table->string('assist2_id')->nullable();
            $table->string('assist2_name')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->string('in_player_id')->nullable();
            $table->string('in_player_name')->nullable();
            $table->string('out_player_id')->nullable();
            $table->string('out_player_name')->nullable();
            $table->integer('var_reason')->default(0);
            $table->integer('var_result')->default(0);
            $table->integer('reason_type')->nullable();
            $table->unique(['match_id', 'player_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_incidents');
    }
};
