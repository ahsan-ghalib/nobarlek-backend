<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatchInformationRequest;
use App\Http\Requests\UpdateMatchInformationRequest;
use App\Models\MatchInformation;

class MatchInformationController extends Controller
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
    public function store(StoreMatchInformationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MatchInformation $matchInformation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMatchInformationRequest $request, MatchInformation $matchInformation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MatchInformation $matchInformation)
    {
        //
    }
}
