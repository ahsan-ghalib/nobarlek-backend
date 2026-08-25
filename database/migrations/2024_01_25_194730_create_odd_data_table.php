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
        Schema::create('odd_data', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->index();
            $table->string('match_id')->index();
            $table->string('type')->index();
            $table->bigInteger('change_time');
            $table->integer('match_time');
            $table->string('home_draw_away');
            $table->string('match_status')->index();
            $table->string('goal_score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odd_data');
    }
};
