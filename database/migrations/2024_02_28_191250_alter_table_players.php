<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->after('name', function (Blueprint $table) {
                $table->string('name_aa')->nullable();
                $table->string('name_nl')->nullable();
                $table->string('name_pt')->nullable();
                $table->string('name_br')->nullable();
                $table->string('name_es')->nullable();
                $table->string('name_fr')->nullable();
                $table->string('name_de')->nullable();
                $table->string('name_vi')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'name_aa',
                'name_nl',
                'name_pt',
                'name_br',
                'name_es',
                'name_fr',
                'name_de',
                'name_vi',
            ]);
        });
    }
};
