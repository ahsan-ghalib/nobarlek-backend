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
        Schema::create('player_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('player_id')->index();
            $table->string('from_team_id')->index()->nullable();
            $table->string('from_team_name');
            $table->string('to_team_id')->index()->nullable();
            $table->string('to_team_name');
            $table->integer('transfer_type');
            $table->integer('transfer_time');
            $table->integer('transfer_fee');
            $table->string('transfer_desc')->nullable();

            $table->unique(['player_id', 'from_team_id', 'to_team_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_transfers');
    }
};
