<?php

namespace App\Http\Controllers\Dashboard\Players;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.players');
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $indonesiaCountryId = Country::query()
            ->whereRaw('LOWER(name) = ?', ['indonesia'])
            ->value('country_id');

        $players = Player::query()
            ->select(['id', 'player_id', 'name', 'short_name', 'logo', 'team_id', 'seo_level'])
            ->when($indonesiaCountryId, fn ($q) => $q->where('country_id', $indonesiaCountryId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('short_name', 'like', "%{$q}%")
                        ->orWhere('player_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $players,
            'message' => 'Successfully retrieved players',
        ]);
    }

    public function show(Player $player): JsonResponse
    {
        return response()->json([
            'data' => $player,
            'message' => 'Successfully retrieved player',
        ]);
    }
}

