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
        Schema::create('football_data', function (Blueprint $table) {
            $table->id();
            $table->string('data_type')->index();
            $table->string('match_id')->nullable()->index();
            $table->string('season_id')->index()->nullable();
            $table->bigInteger('pub_time')->nullable();
            $table->bigInteger('update_time')->index();
            $table->timestamps();

            $table->unique(['data_type', 'match_id', 'season_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('football_data');
    }
};
