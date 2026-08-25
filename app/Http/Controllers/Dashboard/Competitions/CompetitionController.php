<?php

namespace App\Http\Controllers\Dashboard\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.competitions');
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $competitions = Competition::query()
            ->select(['id', 'competition_id', 'name', 'short_name', 'logo', 'type', 'seo_level'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('short_name', 'like', "%{$q}%")
                        ->orWhere('competition_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $competitions,
            'message' => 'Successfully retrieved competitions',
        ]);
    }

    public function show(Competition $competition): JsonResponse
    {
        return response()->json([
            'data' => $competition,
            'message' => 'Successfully retrieved competition',
        ]);
    }
}

