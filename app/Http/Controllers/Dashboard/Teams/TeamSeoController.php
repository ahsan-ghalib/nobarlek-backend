<?php

namespace App\Http\Controllers\Dashboard\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateTeamSeoRequest;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class TeamSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.teams');
    }

    public function __invoke(UpdateTeamSeoRequest $request, Team $team)
    {
        $team->update($request->validated());

        if (!$team->wasChanged()) {
            return response()->json([
                'data' => $team,
                'message' => 'Team SEO not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $team,
            'message' => 'Successfully team SEO updated',
        ]);
    }
}

