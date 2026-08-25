<?php

namespace App\Http\Controllers\Dashboard\Matches;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateFootballMatchSeoRequest;
use App\Models\FootballMatch;

class FootballMatchSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.matches');
    }

    public function __invoke(UpdateFootballMatchSeoRequest $request, FootballMatch $footballMatch)
    {
        $footballMatch->update($request->validated());
        $changed = $footballMatch->wasChanged();
        $footballMatch->refresh();

        return response()->json([
            'data' => $footballMatch,
            'message' => $changed
                ? 'Successfully match SEO updated'
                : 'Match SEO saved (no changes detected)',
        ]);
    }
}

