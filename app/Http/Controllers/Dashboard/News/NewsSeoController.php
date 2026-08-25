<?php

namespace App\Http\Controllers\Dashboard\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNewsSeoRequest;
use App\Models\News;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:news.update');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateNewsSeoRequest $request, News $news)
    {
        $news->update($request->validated());

        if (!$news->wasChanged()) {
            return response()->json([
                'data' => $news,
                'message' => 'Error updating news',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news updated',
        ]);
    }
}
