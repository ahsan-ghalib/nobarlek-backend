<?php

namespace App\Http\Controllers\Dashboard\Players;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdatePlayerImageRequest;
use App\Models\Player;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PlayerImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.players');
    }

    public function __invoke(UpdatePlayerImageRequest $request, Player $player)
    {
        $path = Storage::disk('public')->put('/players', $request->file('logo'));

        $player->update(['logo' => $path]);

        if (!$player->wasChanged()) {
            return response()->json([
                'data' => $player,
                'message' => 'Player image not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $player,
            'message' => 'Successfully player image updated',
        ]);
    }
}

