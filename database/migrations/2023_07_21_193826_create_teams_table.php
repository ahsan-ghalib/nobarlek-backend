<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->unique()->index();
            $table->string('competition_id')->nullable();
            $table->string('country_id')->nullable();
            $table->string('name');
            $table->string('short_name');
            $table->string('logo');
            $table->boolean('national');
            $table->string('country_logo');
            $table->integer('foundation_time');
            $table->string('website')->nullable();
            $table->string('coach_id')->nullable();
            $table->string('venue_id')->nullable();
            $table->double('market_value');
            $table->string('market_value_currency');
            $table->integer('total_players');
            $table->integer('foreign_players');
            $table->integer('national_players');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
