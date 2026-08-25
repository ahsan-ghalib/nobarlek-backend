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
        Schema::create('match_line_ups', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('player_id')->index();
            $table->string('match_id')->index();
            $table->boolean('first');
            $table->boolean('captain');
            $table->string('name');
            $table->string('logo');
            $table->integer('shirt_number');
            $table->string('position');
            $table->integer('x');
            $table->integer('y');
            $table->string('rating');
            $table->timestamps();
            $table->unique(['player_id', 'match_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_line_ups');
    }
};
