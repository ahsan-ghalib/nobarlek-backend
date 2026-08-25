<?php

namespace App\Http\Controllers\Dashboard\Players;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdatePlayerAboutRequest;
use App\Models\Player;
use Symfony\Component\HttpFoundation\Response;

class PlayerAboutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.players');
    }

    public function __invoke(UpdatePlayerAboutRequest $request, Player $player)
    {
        $player->update($request->validated());

        if (! $player->wasChanged()) {
            return response()->json([
                'data' => $player,
                'message' => 'Player about not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $player,
            'message' => 'Successfully updated player about',
        ]);
    }
}
