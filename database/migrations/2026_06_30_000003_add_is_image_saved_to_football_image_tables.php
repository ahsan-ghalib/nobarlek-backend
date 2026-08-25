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
        Schema::table('competitions', function (Blueprint $table) {
            $table->boolean('is_image_saved')->default(false)->after('logo');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_image_saved')->default(false)->after('logo');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_image_saved')->default(false)->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('is_image_saved');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('is_image_saved');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('is_image_saved');
        });
    }
};
