<?php

namespace App\Http\Controllers\Football\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompetitionNewsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Competition $competition)
    {
        return $this->jsonResponse($competition->news()->latest()->limit(10)->get(), Response::HTTP_OK);
    }
}
