<?php

namespace App\Http\Controllers\Football\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Support\ScrapingCountryScope;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamController extends Controller
{
    /**
     * Paginated team list (GET /api/football/teams).
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);

        $query = Team::query()
            ->tap(fn ($q) => ScrapingCountryScope::restrictTeamsQuery($q))
            ->select([
                'team_id',
                'competition_id',
                'country_id',
                'name',
                'name_aa',
                'name_nl',
                'name_vi',
                'name_pt',
                'name_br',
                'name_es',
                'name_fr',
                'name_de',
                'short_name',
                'logo',
            ])
            ->with([
                'country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'competition:competition_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de',
            ])
            ->when($request->filled('competition_id'), fn ($q) => $q->where('competition_id', $request->query('competition_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->query('q').'%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('short_name', 'like', $term);
                });
            })
            ->orderBy('name');

        $teams = $query->paginate($perPage);

        return response()->json(
            [
                'message' => 'Successfully retrieved data',
                'data' => $teams,
            ],
            Response::HTTP_OK,
        );
    }
}
