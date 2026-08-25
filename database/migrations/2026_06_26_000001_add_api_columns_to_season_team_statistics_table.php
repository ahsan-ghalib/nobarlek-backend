<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns `season_team_statistics` with sports API season team stat fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $anchor = 'poss_losts';

        Schema::table('season_team_statistics', function (Blueprint $table) use (&$anchor) {
            foreach ([
                'saves',
                'penalty_conceded',
                'aerial_won',
                'aerial_lost',
                'ground_won',
                'ground_lost',
                'big_chance_created',
                'big_chance_missed',
            ] as $column) {
                if (! Schema::hasColumn('season_team_statistics', $column)) {
                    $table->integer($column)->default(0)->after($anchor);
                    $anchor = $column;
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('season_team_statistics', function (Blueprint $table) {
            $columns = [
                'saves',
                'penalty_conceded',
                'aerial_won',
                'aerial_lost',
                'ground_won',
                'ground_lost',
                'big_chance_created',
                'big_chance_missed',
            ];
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('season_team_statistics', $col));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
