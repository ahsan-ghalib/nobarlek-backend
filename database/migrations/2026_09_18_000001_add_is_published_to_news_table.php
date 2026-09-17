<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('is_top');
            }
        });

        if (Schema::hasColumn('news', 'status')) {
            DB::table('news')->where('status', 'published')->update(['is_published' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};

