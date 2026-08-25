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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('national_players');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->unsignedTinyInteger('seo_level')->nullable()->index()->after('meta_keywords');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('positions');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->unsignedTinyInteger('seo_level')->nullable()->index()->after('meta_keywords');
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('priority');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->unsignedTinyInteger('seo_level')->nullable()->index()->after('meta_keywords');
        });

        Schema::table('football_matches', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('environment');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->unsignedTinyInteger('seo_level')->nullable()->index()->after('meta_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'seo_level']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'seo_level']);
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'seo_level']);
        });

        Schema::table('football_matches', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'seo_level']);
        });
    }
};

