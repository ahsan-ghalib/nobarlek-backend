<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_injuries', function (Blueprint $table) {
            $table->string('injury_id')->nullable()->after('match_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('match_injuries', function (Blueprint $table) {
            $table->dropColumn('injury_id');
        });
    }
};
