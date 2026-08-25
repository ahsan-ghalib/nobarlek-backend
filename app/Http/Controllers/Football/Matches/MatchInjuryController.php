<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatchInjuryRequest;
use App\Http\Requests\UpdateMatchInjuryRequest;
use App\Models\MatchInjury;

class MatchInjuryController extends Controller
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
    public function store(StoreMatchInjuryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MatchInjury $matchInjury)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMatchInjuryRequest $request, MatchInjury $matchInjury)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MatchInjury $matchInjury)
    {
        //
    }
}
