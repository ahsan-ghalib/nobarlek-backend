<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sports API team_stats may include big-chance and ground-duel fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $anchor = Schema::hasColumn('match_team_statistics', 'duel_lost')
            ? 'duel_lost'
            : 'poss_losts';

        Schema::table('match_team_statistics', function (Blueprint $table) use (&$anchor) {
            if (! Schema::hasColumn('match_team_statistics', 'big_chance_created')) {
                $table->integer('big_chance_created')->default(0)->after($anchor);
                $anchor = 'big_chance_created';
            }
            if (! Schema::hasColumn('match_team_statistics', 'big_chance_missed')) {
                $table->integer('big_chance_missed')->default(0)->after($anchor);
                $anchor = 'big_chance_missed';
            }
            if (! Schema::hasColumn('match_team_statistics', 'ground_won')) {
                $table->integer('ground_won')->default(0)->after($anchor);
                $anchor = 'ground_won';
            }
            if (! Schema::hasColumn('match_team_statistics', 'ground_lost')) {
                $table->integer('ground_lost')->default(0)->after($anchor);
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_team_statistics', function (Blueprint $table) {
            $columns = ['big_chance_created', 'big_chance_missed', 'ground_won', 'ground_lost'];
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('match_team_statistics', $col));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
