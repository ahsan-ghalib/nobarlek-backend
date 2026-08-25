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
        Schema::create('match_injuries', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('player_id')->index();
            $table->string('match_id')->index();
            $table->string('name');
            $table->string('position');
            $table->string('logo');
            $table->string('reason');
            $table->integer('start_time');
            $table->integer('end_time');
            $table->integer('missed_matches');
            $table->timestamps();

            $table->unique(['player_id', 'match_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_injuries');
    }
};
