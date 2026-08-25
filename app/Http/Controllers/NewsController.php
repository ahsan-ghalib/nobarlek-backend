<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Repositories\NewsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    private NewsRepository $newsRepository;
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 24), 1), 50);

        return response()->json([
            'data' => [
                'top_news' => $this->newsRepository->fetchTopNews(),
                'news' => $this->newsRepository->fetchNews()->isPublished()->paginate($perPage),
                'most_read_news' => $this->newsRepository->fetchMostReadNews()
            ],
            'message' => 'Successfully retrieved news',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = News::query()->create($request->validated());

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news created',
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(News $news): JsonResponse
    {
        return response()->json([
            'data' => [
                'related_news' => $this->newsRepository->fetchRelatedNews($news->meta_keywords, $news->id),
                'news' => $news,
                'most_read_news' => $this->newsRepository->fetchMostReadNews()
            ],
            'message' => 'Successfully retrieved news',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $news->update($request->validated());

        if (!$news->wasChanged()) {
            return response()->json([
                'data' => $news,
                'message' => 'News not updated',
            ]);
        }

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news created',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news): JsonResponse
    {
        $news->delete();

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news deleted',
        ]);
    }
}
