<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetitionPrioritySeeder extends Seeder
{
    /**
     * Names that identify the Indonesian top tier for priority 1 (case-insensitive).
     *
     * @var list<string>
     */
    private const INDONESIAN_SUPER_LEAGUE_NAMES = [
        'Indonesian Super League',
        'Indonesia Super League',
    ];

    /**
     * Assign display order: Indonesian Super League first, then all others by name A–Z.
     */
    public function run(): void
    {
        $isl = $this->resolveIndonesianSuperLeague();

        $orderedIds = collect();

        if ($isl !== null) {
            $orderedIds->push($isl->competition_id);
        }

        $orderedIds = $orderedIds->merge(
            Competition::query()
                ->when($isl !== null, fn ($q) => $q->where('competition_id', '!=', $isl->competition_id))
                ->orderBy('name')
                ->pluck('competition_id')
        );

        if ($orderedIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds->values() as $index => $competitionId) {
                Competition::query()
                    ->where('competition_id', $competitionId)
                    ->update(['priority' => $index + 1]);
            }
        });
    }

    private function resolveIndonesianSuperLeague(): ?Competition
    {
        foreach (self::INDONESIAN_SUPER_LEAGUE_NAMES as $name) {
            $normalized = mb_strtolower($name, 'UTF-8');

            $found = Competition::query()
                ->whereRaw('LOWER(name) = ?', [$normalized])
                ->first();

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }
}
