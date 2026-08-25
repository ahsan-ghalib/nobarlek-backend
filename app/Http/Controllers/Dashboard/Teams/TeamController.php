<?php

namespace App\Http\Controllers\Dashboard\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.teams');
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $teams = Team::query()
            ->select(['id', 'team_id', 'name', 'short_name', 'logo', 'seo_level'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('short_name', 'like', "%{$q}%")
                        ->orWhere('team_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $teams,
            'message' => 'Successfully retrieved teams',
        ]);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json([
            'data' => $team,
            'message' => 'Successfully retrieved team',
        ]);
    }
}

