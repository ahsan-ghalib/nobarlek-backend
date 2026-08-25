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
        Schema::create('match_streams', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->unique()->index();
            $table->unsignedTinyInteger('sport_id')->default(1);
            $table->unsignedBigInteger('match_time')->nullable();
            $table->text('pushurl1')->nullable();
            $table->text('pushurl2')->nullable();
            $table->text('playback_url')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_streams');
    }
};
