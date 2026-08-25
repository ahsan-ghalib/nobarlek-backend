<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\ResponseMaking\OddsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchOverviewOddsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, FootballMatch $footballMatch, int $companyId)
    {

        $oddsService = (new OddsService($footballMatch->match_id, $companyId));

        $data = [
            'opening_odds' => $oddsService->getOpeningOdds(),
            'pre_match_odds' => $oddsService->getPreMatchOdds(),
            'in_playing_odds' => $oddsService->getInPlayOdds(),
        ];

        return $this->jsonResponse($data, Response::HTTP_OK);
    }
}
