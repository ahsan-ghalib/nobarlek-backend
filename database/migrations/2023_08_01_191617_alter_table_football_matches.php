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
        Schema::table('football_matches', function (Blueprint $table) {
            $table->after('environment',function (Blueprint $table){
                $table->string('away_formation')->nullable();
                $table->string('home_formation')->nullable();
                $table->string('away_coach_id')->nullable();
                $table->string('home_coach_id')->nullable();
                $table->string('confirmed')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('football_matches', function (Blueprint $table) {
            $table->dropColumn([
                'away_formation',
                'home_formation',
                'away_coach_id',
                'home_coach_id',
                'confirmed',
            ]);
        });
    }
};
