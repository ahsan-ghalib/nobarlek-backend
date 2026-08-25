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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('player_id')->unique()->index();
            $table->string('team_id')->nullable();
            $table->string('name');
            $table->string('short_name');
            $table->string('logo');
            $table->string('national_logo');
            $table->integer('age');
            $table->string('birthday');
            $table->float('weight');
            $table->float('height');
            $table->string('country_id')->nullable();
            $table->string('nationality');
            $table->string('market_value');
            $table->string('market_value_currency');
            $table->integer('contract_until');
            $table->string('preferred_foot');
            $table->text('ability');
            $table->text('characteristics');
            $table->string('position');
            $table->text('positions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
