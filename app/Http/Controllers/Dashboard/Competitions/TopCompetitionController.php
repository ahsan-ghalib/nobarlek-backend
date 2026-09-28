<?php

namespace App\Http\Controllers\Dashboard\Competitions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Football\Competitions\SidebarCompetitionController;
use App\Http\Requests\Dashboard\UpdateTopCompetitionsRequest;
use App\Models\Competition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopCompetitionController extends Controller
{
    private const COLUMNS = ['id', 'competition_id', 'name', 'short_name', 'logo', 'top_order'];

    public function __construct()
    {
        $this->middleware('permission:competitions.top');
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->topCompetitions(),
            'message' => 'Successfully retrieved top competitions',
        ]);
    }

    public function update(UpdateTopCompetitionsRequest $request): JsonResponse
    {
        $ids = $request->validated('competition_ids');

        DB::transaction(function () use ($ids) {
            Competition::query()
                ->whereNotNull('top_order')
                ->whereNotIn('competition_id', $ids)
                ->update(['top_order' => null]);

            foreach ($ids as $index => $id) {
                Competition::query()->where('competition_id', $id)->update(['top_order' => $index + 1]);
            }
        });

        SidebarCompetitionController::forgetCache();

        return response()->json([
            'data' => $this->topCompetitions(),
            'message' => 'Successfully top competitions updated',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $competitions = Competition::query()
            ->select(self::COLUMNS)
            ->whereNull('top_order')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('short_name', 'like', "%{$q}%")
                        ->orWhere('competition_id', 'like', "%{$q}%");
                });
            })
            ->orderedBySequence()
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $competitions,
            'message' => 'Successfully retrieved competitions',
        ]);
    }

    private function topCompetitions()
    {
        return Competition::query()
            ->select(self::COLUMNS)
            ->whereNotNull('top_order')
            ->orderBy('top_order')
            ->get();
    }
}
