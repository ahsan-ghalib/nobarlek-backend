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
        Schema::create('match_chart_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->unique()->index();
            $table->text('count');
            $table->text('per');
            $table->text('timeline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_chart_statistics');
    }
};
