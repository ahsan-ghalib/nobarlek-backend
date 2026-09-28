<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MatchIncidents extends Model
{
    use HasFactory;

    protected $casts = [
        'time' => 'integer',
    ];

    protected $fillable = [
        'type',
        'position',
        'time',
        'match_id',
        'player_id',
        'player_name',
        'assist1_id',
        'assist1_name',
        'assist2_id',
        'assist2_name',
        'home_score',
        'away_score',
        'in_player_id',
        'in_player_name',
        'out_player_id',
        'out_player_name',
        'var_reason',
        'var_result',
        'reason_type',
    ];

    /**
     * TheSports sends a match's full incident list on every update and later
     * corrects minutes and players (e.g. a goal at 33' becomes 34', or gains a
     * player_id). Merging by a unique key kept the stale row next to the
     * corrected one, so the stored list is replaced as a whole instead.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function replaceForMatch(string $matchId, array $rows): void
    {
        $columns = (new static)->getFillable();
        $normalize = fn (array $row) => array_map(
            fn ($v) => $v === null ? '' : (string) $v,
            array_intersect_key($row, array_flip($columns)),
        );

        $incoming = array_map(fn (array $row) => $normalize(array_merge(array_fill_keys($columns, null), $row, ['match_id' => $matchId])), $rows);

        $stored = static::query()
            ->where('match_id', $matchId)
            ->orderBy('id')
            ->get($columns)
            ->map(fn (self $incident) => $normalize(array_merge(array_fill_keys($columns, null), $incident->getAttributes())))
            ->all();

        // Live polling runs every 30s; skip the rewrite when nothing changed.
        if ($stored === $incoming) {
            return;
        }

        $now = now();
        $inserts = array_map(fn (array $row) => array_merge($row, [
            'match_id' => $matchId,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $rows);

        DB::transaction(function () use ($matchId, $inserts) {
            static::query()->where('match_id', $matchId)->delete();
            static::query()->insert($inserts);
        });
    }
}
