<?php

namespace App\Http\Controllers\Dashboard\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateTeamAboutRequest;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Response;

class TeamAboutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.teams');
    }

    public function __invoke(UpdateTeamAboutRequest $request, Team $team)
    {
        $team->update($request->validated());

        if (! $team->wasChanged()) {
            return response()->json([
                'data' => $team,
                'message' => 'Team about not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $team,
            'message' => 'Successfully updated team about',
        ]);
    }
}
