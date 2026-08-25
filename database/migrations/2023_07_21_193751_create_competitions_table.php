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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('competition_id')->unique()->index();
            $table->string('category_id')->nullable();
            $table->string('country_id')->nullable();
            $table->string('name');
            $table->string('short_name');
            $table->string('logo');
            $table->string('type');
            $table->string('cur_season_id')->nullable();
            $table->string('cur_stage_id')->nullable();
            $table->string('cur_round');
            $table->string('round_count');
            $table->text('title_holder')->nullable();
            $table->text('most_titles')->nullable();
            $table->text('newcomers')->nullable();
            $table->text('divisions')->nullable();
            $table->text('host')->nullable();
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
