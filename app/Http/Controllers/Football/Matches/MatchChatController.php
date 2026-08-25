<?php

namespace App\Http\Controllers\Football\Matches;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatchChatRequest;
use App\Models\FootballMatch;
use Symfony\Component\HttpFoundation\Response;

class MatchChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FootballMatch $footballMatch)
    {
        $matchChat = $footballMatch->matchChat()
            ->with([
                'user:id,name,avatar',
            ])
            ->latest()
            ->paginate();

        return $this->jsonResponse(collect($matchChat), Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMatchChatRequest $request, FootballMatch $footballMatch)
    {
        $message = $footballMatch->matchChat()
            ->create([
                'message' => $request->validated()['message'],
                'user_id' => auth()->id(),
            ]);

        return $this->jsonResponse(collect($message), Response::HTTP_CREATED);
    }
}
