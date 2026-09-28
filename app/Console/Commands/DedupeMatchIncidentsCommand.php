<?php

namespace App\Console\Commands;

use App\Enums\TechnicalStatisticsEnum;
use App\Models\MatchIncidents;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * One-off cleanup for incidents stored before MatchIncidents::replaceForMatch: the old
 * upsert kept a stale row every time the feed corrected a minute or a player, so the
 * same goal or substitution shows up twice. Live matches heal on their next poll;
 * this removes the leftovers from finished matches.
 */
class DedupeMatchIncidentsCommand extends Command
{
    protected $signature = 'match-incidents:dedupe
        {--match= : Only clean this match ID}
        {--dry-run : Report what would be deleted without deleting}';

    protected $description = 'Remove duplicate goal and substitution incidents left by feed corrections';

    private const GOAL_TYPES = [
        TechnicalStatisticsEnum::GOAL->value,
        TechnicalStatisticsEnum::OWN_GOAL->value,
        TechnicalStatisticsEnum::PENALTY->value,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;
        $matches = 0;

        MatchIncidents::query()
            ->select('match_id')
            ->when($this->option('match'), fn ($q, $matchId) => $q->where('match_id', $matchId))
            ->whereIn('type', [...self::GOAL_TYPES, TechnicalStatisticsEnum::SUBSTITUTION->value])
            ->groupBy('match_id')
            ->orderBy('match_id')
            ->chunk(500, function (Collection $rows) use ($dryRun, &$deleted, &$matches) {
                foreach ($rows as $row) {
                    $ids = $this->duplicateIds($row->match_id);
                    if ($ids === []) {
                        continue;
                    }

                    $matches++;
                    $deleted += count($ids);
                    if (! $dryRun) {
                        MatchIncidents::query()->whereIn('id', $ids)->delete();
                    }
                }
            });

        $this->info(($dryRun ? 'Would delete' : 'Deleted')." {$deleted} duplicate incidents in {$matches} matches.");

        return self::SUCCESS;
    }

    /**
     * @return list<int>
     */
    private function duplicateIds(string $matchId): array
    {
        $incidents = MatchIncidents::query()
            ->where('match_id', $matchId)
            ->whereIn('type', [...self::GOAL_TYPES, TechnicalStatisticsEnum::SUBSTITUTION->value])
            ->get();

        // Each goal moves the score, so side + resulting score identifies a goal.
        // Rows without a score (0-0) can't be told apart and are left alone.
        $goals = $incidents
            ->filter(fn (MatchIncidents $i) => in_array($i->type, self::GOAL_TYPES, true)
                && (int) $i->home_score + (int) $i->away_score > 0)
            ->groupBy(fn (MatchIncidents $i) => implode('|', [$i->position, $i->home_score, $i->away_score]));

        // A player is only brought on once, so the in/out pair identifies a substitution.
        $subs = $incidents
            ->filter(fn (MatchIncidents $i) => $i->type === TechnicalStatisticsEnum::SUBSTITUTION->value
                && trim((string) $i->in_player_id) !== ''
                && trim((string) $i->out_player_id) !== '')
            ->groupBy(fn (MatchIncidents $i) => implode('|', [$i->position, $i->in_player_id, $i->out_player_id]));

        return $goals->concat($subs)
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->flatMap(function (Collection $group) {
                // Keep the corrected row: prefer one that names the player, then the newest.
                $keep = $group->sortByDesc(fn (MatchIncidents $i) => [
                    trim((string) $i->player_id) !== '' ? 1 : 0,
                    $i->id,
                ])->first();

                return $group->where('id', '!=', $keep->id)->pluck('id');
            })
            ->values()
            ->all();
    }
}
