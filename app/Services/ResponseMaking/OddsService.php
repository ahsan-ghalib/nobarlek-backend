<?php

namespace App\Services\ResponseMaking;

use App\Enums\OddsTypeEnum;
use App\Models\OddData;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OddsService
{
    public int $companyId;
    public string $matchId;

    public function __construct($matchId, $companyId)
    {
        $this->matchId = $matchId;
        $this->companyId = $companyId;
    }

    public function getQuery(): Builder
    {
        return OddData::query()
            ->where('match_id', $this->matchId)
            ->where('company_id', '=', $this->companyId);
    }

    public function getStatusQuery(OddsTypeEnum $oddsTypeEnum): Builder
    {
        return $this->getQuery()
            ->where('type', '=', $oddsTypeEnum->value)
            ->latest('change_time');
    }

    public function getOpeningOdds(): array
    {
        $query = $this->getQuery()->oldest('change_time');

        $opening1x2Odds = (clone $query)
            ->where('type', '=', OddsTypeEnum::X_1_2->value)
            ->first();

        $openingAsiaOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Asia_Handicap->value)
            ->first();

        $openingGoalsOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Total_Goals->value)
            ->first();

        $openingCornersOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Corner_Kicks->value)
            ->first();

        return [
            'x_12' => $opening1x2Odds,
            'asia' => $openingAsiaOdds,
            'total_goals' => $openingGoalsOdds,
            'total_corners' => $openingCornersOdds
        ];
    }

    public function getPreMatchOdds(): array
    {
        $query = $this->getQuery()->latest('change_time');

        $preMatch1x2Odds = (clone $query)
            ->where('type', '=', OddsTypeEnum::X_1_2->value)
            ->where('match_status','=', 1)
            ->first();

        $preMatchAsiaOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Asia_Handicap->value)
            ->where('match_status','=', 1)
            ->first();

        $preMatchGoalsOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Total_Goals->value)
            ->where('match_status','=', 1)
            ->first();

        $preMatchCornersOdds = (clone $query)
            ->where('type', '=', OddsTypeEnum::Corner_Kicks->value)
            ->where('match_status','=', 1)
            ->first();

        return [
            'x_12' => $preMatch1x2Odds,
            'asia' => $preMatchAsiaOdds,
            'total_goals' => $preMatchGoalsOdds,
            'total_corners' => $preMatchCornersOdds
        ];
    }

    public function getInPlayOdds(): array
    {
        $query = $this->getQuery();

        $inPlaying1x2Odds = $this->getStatusQuery(OddsTypeEnum::X_1_2)
            ->first();

        $inPlayingAsiaOdds = $this->getStatusQuery(OddsTypeEnum::Asia_Handicap)
            ->first();

        $inPlayingGoalsOdds = $this->getStatusQuery(OddsTypeEnum::Total_Goals)
            ->first();

        $inPlayingCornersOdds = $this->getStatusQuery(OddsTypeEnum::Corner_Kicks)
            ->first();

        return [
            'x_12' => $inPlaying1x2Odds,
            'asia' => $inPlayingAsiaOdds,
            'total_goals' => $inPlayingGoalsOdds,
            'total_corners' => $inPlayingCornersOdds
        ];
    }

    public function getFullOddsByStatus(OddsTypeEnum $oddsTypeEnum): Collection
    {
        return $this->getStatusQuery($oddsTypeEnum)
            ->get();
    }
}
