<?php

namespace App\Http\Controllers\Dashboard\Competitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateCompetitionSeoRequest;
use App\Models\Competition;
use Symfony\Component\HttpFoundation\Response;

class CompetitionSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.competitions');
    }

    public function __invoke(UpdateCompetitionSeoRequest $request, Competition $competition)
    {
        $competition->update($request->validated());

        if (!$competition->wasChanged()) {
            return response()->json([
                'data' => $competition,
                'message' => 'Competition SEO not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $competition,
            'message' => 'Successfully competition SEO updated',
        ]);
    }
}

