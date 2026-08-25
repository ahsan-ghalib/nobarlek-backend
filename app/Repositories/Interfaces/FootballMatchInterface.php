<?php

namespace App\Repositories\Interfaces;

use App\Enums\MatchScheduleStatusEnum;
use App\Models\Team;

interface FootballMatchInterface
{
    public function getSingleTeamMatches(Team $team,MatchScheduleStatusEnum $statusEnum, string $month);

}
