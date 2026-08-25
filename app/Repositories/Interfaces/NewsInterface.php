<?php

namespace App\Repositories\Interfaces;

use App\Enums\MatchScheduleStatusEnum;
use App\Models\Team;

interface NewsInterface
{
    public function newsQuery();
    public function fetchNews();
    public function fetchTopNews();
    public function fetchMostReadNews();

}
