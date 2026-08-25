<?php

namespace App\Http\Controllers\Dashboard\Competitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateCompetitionImageRequest;
use App\Models\Competition;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CompetitionImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.competitions');
    }

    public function __invoke(UpdateCompetitionImageRequest $request, Competition $competition)
    {
        $path = Storage::disk('public')->put('/competitions', $request->file('logo'));

        $competition->update(['logo' => $path]);

        if (!$competition->wasChanged()) {
            return response()->json([
                'data' => $competition,
                'message' => 'Competition image not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $competition,
            'message' => 'Successfully competition image updated',
        ]);
    }
}

