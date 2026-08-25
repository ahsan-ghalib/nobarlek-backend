<?php

namespace App\Http\Controllers\Dashboard\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateTeamImageRequest;
use App\Models\Team;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TeamImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.teams');
    }

    public function __invoke(UpdateTeamImageRequest $request, Team $team)
    {
        $path = Storage::disk('public')->put('/teams', $request->file('logo'));

        $team->update(['logo' => $path]);

        if (!$team->wasChanged()) {
            return response()->json([
                'data' => $team,
                'message' => 'Team image not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $team,
            'message' => 'Successfully team image updated',
        ]);
    }
}

