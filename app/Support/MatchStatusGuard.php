<?php

namespace App\Support;

use App\Models\FootballMatch;

/**
 * Diary and recent-list feeds lag behind the live score feed. Applying them
 * blindly sets a match that has already kicked off back to "Not started" and
 * wipes the score. A later status may replace an earlier one; an earlier one
 * may not.
 */
class MatchStatusGuard
{
    /** Higher means the match has progressed. States omitted here are always applied. */
    private const RANK = [
        'Abnormal(suggest hiding)' => 0,
        'Not started' => 0,
        'To be determined' => 0,
        'First half' => 2,
        'Half-time' => 3,
        'Second half' => 4,
        'Overtime' => 5,
        'Overtime(deprecated)' => 5,
        'Penalty Shoot-out' => 6,
        'End' => 8,
    ];

    public static function wouldRegress(string $stored, string $incoming): bool
    {
        if (! isset(self::RANK[$stored], self::RANK[$incoming])) {
            return false;
        }

        return self::RANK[$incoming] < self::RANK[$stored];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public static function protect(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (string) ($row['match_id'] ?? '');
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        if ($ids === []) {
            return $rows;
        }

        $stored = FootballMatch::query()
            ->whereIn('match_id', $ids)
            ->get(['match_id', 'status_id', 'home_scores', 'away_scores'])
            ->keyBy('match_id');

        foreach ($rows as &$row) {
            $current = $stored->get((string) ($row['match_id'] ?? ''));
            if ($current === null) {
                continue;
            }
            $existing = (string) $current->status_id;
            $incoming = (string) ($row['status_id'] ?? '');
            if (! self::wouldRegress($existing, $incoming)) {
                continue;
            }
            $row['status_id'] = $existing;
            $row['home_scores'] = json_encode(is_array($current->home_scores) ? $current->home_scores : []);
            $row['away_scores'] = json_encode(is_array($current->away_scores) ? $current->away_scores : []);
        }
        unset($row);

        return $rows;
    }
}
