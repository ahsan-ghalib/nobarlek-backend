<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatchTeamStatisticRequest;
use App\Http\Requests\UpdateMatchTeamStatisticRequest;
use App\Models\MatchTeamStatistic;

class MatchTeamStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMatchTeamStatisticRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MatchTeamStatistic $matchTeamStatistic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMatchTeamStatisticRequest $request, MatchTeamStatistic $matchTeamStatistic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MatchTeamStatistic $matchTeamStatistic)
    {
        //
    }
}
