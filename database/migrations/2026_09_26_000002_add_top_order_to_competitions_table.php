<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            if (!Schema::hasColumn('competitions', 'top_order')) {
                // Null = not a top competition; otherwise position in the top list (lower first).
                $table->unsignedInteger('top_order')->nullable()->after('priority')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            if (Schema::hasColumn('competitions', 'top_order')) {
                $table->dropColumn('top_order');
            }
        });
    }
};
