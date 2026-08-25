<?php

namespace App\Http\Controllers\Football\Matches;

use App\Enums\OddsTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\ResponseMaking\OddsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchOddsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request, FootballMatch $footballMatch)
    {
        $bet365Service = (new OddsService($footballMatch->match_id, 2));
        $bet365Data = [
            'opening_odds' => $bet365Service->getOpeningOdds(),
            'pre_match_odds' => $bet365Service->getPreMatchOdds(),
        ];

        $crownService = (new OddsService($footballMatch->match_id, 3));

        $crownData = [
            'opening_odds' => $crownService->getOpeningOdds(),
            'pre_match_odds' => $crownService->getPreMatchOdds(),
        ];

        $bet10Service = (new OddsService($footballMatch->match_id, 4));

        $bet10Data = [
            'opening_odds' => $bet10Service->getOpeningOdds(),
            'pre_match_odds' => $bet10Service->getPreMatchOdds(),
        ];

        $williamHillsService = (new OddsService($footballMatch->match_id, 9));

        $williamHillsData = [
            'opening_odds' => $williamHillsService->getOpeningOdds(),
            'pre_match_odds' => $williamHillsService->getPreMatchOdds(),
        ];

        return $this->jsonResponse([
            'bet_365' => $bet365Data,
            'crown' => $crownData,
            'bet_10' => $bet10Data,
            'william_hills' => $williamHillsData,
        ], Response::HTTP_OK);
    }

    /**
     * Handle the incoming request.
     */
    public function show(Request $request, FootballMatch $footballMatch, int $companyId, OddsTypeEnum $type): JsonResponse
    {
        $oddsData = (new OddsService($footballMatch->match_id, $companyId))->getFullOddsByStatus($type);

        return $this->jsonResponse($oddsData, Response::HTTP_OK);
    }
}
