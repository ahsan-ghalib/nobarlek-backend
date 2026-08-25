<?php

namespace App\Http\Controllers\Football\Players;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayerNewsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Player $player)
    {
        return $this->jsonResponse($player->news()->latest()->limit(10)->get(), Response::HTTP_OK);
    }
}
