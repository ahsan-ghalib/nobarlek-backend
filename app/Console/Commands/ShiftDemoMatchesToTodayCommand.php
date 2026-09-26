<?php

namespace App\Console\Commands;

use App\Enums\MatchStateEnum;
use App\Models\FootballMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Local/dev helper: when the scraper has no matches for today, move recent finished matches onto
 * today's timeline (live, finished, later today, tomorrow) so the site has something to show.
 * Original values are backed up and can be put back with --restore.
 */
class ShiftDemoMatchesToTodayCommand extends Command
{
    protected $signature = 'demo:shift-matches-to-today
        {--force : Shift even if today already has live matches}
        {--restore : Put back the original values of the last shift}';

    protected $description = 'Move recent finished matches to today (local only) so the homepage is not empty';

    private const BACKUP = 'demo-shift-matches-backup.json';

    private const BACKUP_COLUMNS = ['match_id', 'status_id', 'match_time', 'kickoff_time', 'home_scores', 'away_scores'];

    /**
     * Slots filled in order. Live minutes drive `calculated_match_time` via kickoff_time
     * (first half: kickoff = start of match; second half: kickoff = start of second half).
     */
    private const SLOTS = [
        ['state' => 'live', 'status' => MatchStateEnum::SECOND_HALF, 'minute' => 84],
        ['state' => 'live', 'status' => MatchStateEnum::SECOND_HALF, 'minute' => 67],
        ['state' => 'live', 'status' => MatchStateEnum::HALF_TIME, 'minute' => 45],
        ['state' => 'live', 'status' => MatchStateEnum::FIRST_HALF, 'minute' => 23],
        ['state' => 'live', 'status' => MatchStateEnum::FIRST_HALF, 'minute' => 9],
        ['state' => 'finished', 'hoursAgo' => 2],
        ['state' => 'finished', 'hoursAgo' => 3],
        ['state' => 'finished', 'hoursAgo' => 4],
        ['state' => 'upcoming', 'hoursAhead' => 1],
        ['state' => 'upcoming', 'hoursAhead' => 2],
        ['state' => 'upcoming', 'hoursAhead' => 3],
        ['state' => 'upcoming', 'hoursAhead' => 24],
        ['state' => 'upcoming', 'hoursAhead' => 25],
        ['state' => 'upcoming', 'hoursAhead' => 26],
        ['state' => 'upcoming', 'hoursAhead' => 27],
    ];

    public function handle(): int
    {
        if (!app()->environment('local')) {
            $this->error('This command rewrites match data and only runs when APP_ENV=local.');

            return self::FAILURE;
        }

        return $this->option('restore') ? $this->restore() : $this->shift();
    }

    private function shift(): int
    {
        $liveNow = FootballMatch::query()
            ->whereIn('status_id', [MatchStateEnum::FIRST_HALF->value, MatchStateEnum::SECOND_HALF->value, MatchStateEnum::HALF_TIME->value])
            ->whereBetween('match_time', [now()->subHours(3)->timestamp, now()->timestamp])
            ->count();

        if ($liveNow > 0 && !$this->option('force')) {
            $this->info("Today already has {$liveNow} live matches. Nothing to do (use --force to shift anyway).");

            return self::SUCCESS;
        }

        if (Storage::exists(self::BACKUP)) {
            $this->warn('Restoring the previous shift first so its original values are not lost.');
            $this->restore();
        }

        // Most recent finished matches, spread across competitions for variety.
        $matches = FootballMatch::query()
            ->where('status_id', MatchStateEnum::END->value)
            ->whereNotNull('home_team_id')
            ->whereNotNull('away_team_id')
            ->orderByDesc('match_time')
            ->limit(200)
            ->get([...self::BACKUP_COLUMNS, 'competition_id'])
            ->groupBy('competition_id')
            ->flatMap(fn ($group) => $group->values()->map(fn ($m, $i) => [$i, $m]))
            ->sortBy(fn ($pair) => $pair[0])
            ->map(fn ($pair) => $pair[1])
            ->take(count(self::SLOTS))
            ->values();

        if ($matches->isEmpty()) {
            $this->error('No finished matches found to shift.');

            return self::FAILURE;
        }

        Storage::put(self::BACKUP, json_encode(
            $matches->map(fn (FootballMatch $m) => array_intersect_key($m->getAttributes(), array_flip(self::BACKUP_COLUMNS)))->values(),
            JSON_PRETTY_PRINT
        ));

        $now = now()->timestamp;
        $rows = [];

        DB::transaction(function () use ($matches, $now, &$rows) {
            foreach ($matches as $index => $match) {
                $slot = self::SLOTS[$index];
                $update = $this->slotValues($slot, $match, $now);

                FootballMatch::query()->where('match_id', $match->match_id)->toBase()->update($update);
                $rows[] = [$match->match_id, $update['status_id'], date('Y-m-d H:i', $update['match_time']), $this->label($slot)];
            }
        });

        $this->table(['match_id', 'status', 'match_time (server tz)', 'slot'], $rows);
        $this->info('Shifted ' . count($rows) . ' matches. Undo with: php artisan demo:shift-matches-to-today --restore');
        $this->line('Note: a running live-data scraper may overwrite these rows.');

        return self::SUCCESS;
    }

    private function slotValues(array $slot, FootballMatch $match, int $now): array
    {
        $home = $this->scores($match->getRawOriginal('home_scores'));
        $away = $this->scores($match->getRawOriginal('away_scores'));

        switch ($slot['state']) {
            case 'live':
                $minute = $slot['minute'];
                /** @var MatchStateEnum $status */
                $status = $slot['status'];
                $matchStart = $now - ($minute + ($minute > 45 ? 15 : 0)) * 60;
                $kickoff = $status === MatchStateEnum::SECOND_HALF
                    ? $now - ($minute - 46) * 60
                    : $matchStart;

                // Scale the final score down to a plausible in-game score.
                $home[0] = (int) round(($home[0] ?? 0) * min(1, $minute / 75));
                $away[0] = (int) round(($away[0] ?? 0) * min(1, $minute / 75));

                return [
                    'status_id' => $status->value,
                    'match_time' => $matchStart,
                    'kickoff_time' => $kickoff,
                    'home_scores' => json_encode($home),
                    'away_scores' => json_encode($away),
                ];

            case 'finished':
                $start = $now - $slot['hoursAgo'] * 3600;

                return [
                    'status_id' => MatchStateEnum::END->value,
                    'match_time' => $start,
                    'kickoff_time' => $start,
                    'home_scores' => json_encode($home),
                    'away_scores' => json_encode($away),
                ];

            default: // upcoming
                $start = $now + $slot['hoursAhead'] * 3600;
                $zero = json_encode(array_fill(0, 7, 0));

                return [
                    'status_id' => MatchStateEnum::NOT_STARTED->value,
                    'match_time' => $start,
                    'kickoff_time' => 0,
                    'home_scores' => $zero,
                    'away_scores' => $zero,
                ];
        }
    }

    private function restore(): int
    {
        if (!Storage::exists(self::BACKUP)) {
            $this->info('No backup found — nothing to restore.');

            return self::SUCCESS;
        }

        $rows = json_decode(Storage::get(self::BACKUP), true) ?: [];

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                FootballMatch::query()->where('match_id', $row['match_id'])->toBase()
                    ->update(array_diff_key($row, ['match_id' => true]));
            }
        });

        Storage::delete(self::BACKUP);
        $this->info('Restored ' . count($rows) . ' matches to their original values.');

        return self::SUCCESS;
    }

    private function scores(mixed $raw): array
    {
        $scores = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);

        return array_map('intval', array_pad(array_values($scores), 7, 0));
    }

    private function label(array $slot): string
    {
        return match ($slot['state']) {
            'live' => "live {$slot['minute']}'",
            'finished' => "finished {$slot['hoursAgo']}h ago",
            default => "starts in {$slot['hoursAhead']}h",
        };
    }
}
