<?php

namespace App\Console\Commands;

use App\Models\MatchLineUp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off cleanup for lineups stored before SingleMatchLineupDataScrapingService
 * pruned rows: when the feed swapped a player's id, the old row stayed and showed
 * as a twelfth starter. Every sync touches all rows it sends, so a row older than
 * the match's latest sync was dropped by the feed.
 */
class PruneStaleLineupsCommand extends Command
{
    protected $signature = 'match-lineups:prune-stale
        {--match= : Only clean this match ID}
        {--dry-run : Report what would be deleted without deleting}';

    protected $description = 'Remove lineup rows the feed no longer sends (left by player id corrections)';

    /** Rows written by one sync share a timestamp; this absorbs a slow upsert. */
    private const SYNC_SLACK_SECONDS = 60;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $latest = MatchLineUp::query()
            ->select('match_id', DB::raw('MAX(updated_at) AS synced_at'))
            ->when($this->option('match'), fn ($q, $matchId) => $q->where('match_id', $matchId))
            ->groupBy('match_id');

        $stale = MatchLineUp::query()
            ->from('match_line_ups as l')
            ->joinSub($latest, 'latest', 'latest.match_id', '=', 'l.match_id')
            ->whereRaw('l.updated_at < latest.synced_at - INTERVAL ? SECOND', [self::SYNC_SLACK_SECONDS]);

        $ids = $stale->pluck('l.id');
        $matches = (clone $stale)->distinct()->count('l.match_id');

        if (! $dryRun) {
            $ids->chunk(1000)->each(fn ($chunk) => MatchLineUp::query()->whereIn('id', $chunk->all())->delete());
        }

        $this->info(($dryRun ? 'Would delete' : 'Deleted')." {$ids->count()} stale lineup rows in {$matches} matches.");

        return self::SUCCESS;
    }
}
