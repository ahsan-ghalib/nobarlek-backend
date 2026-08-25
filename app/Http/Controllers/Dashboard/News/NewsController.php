<?php

namespace App\Http\Controllers\Dashboard\News;

use App\Enums\SearchByEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\Competition;
use App\Models\News;
use App\Models\Player;
use App\Models\Team;
use App\Repositories\NewsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    private NewsRepository $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
        $this->middleware('permission:news.view')->only(['index', 'show']);
        $this->middleware('permission:news.create')->only(['store']);
        $this->middleware('permission:news.update')->only(['update']);
        $this->middleware('permission:news.delete')->only(['destroy']);
        $this->middleware('permission:news.publish')->only(['isTop', 'isPublished']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $news = $this->newsRepository
            ->fetchNews()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
            ->paginate(10);

        if ($news->total() === 0) {
            return response()->json([
                'data' => null,
                'message' => 'No data found',
            ], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'data' => $news,
            'message' => 'Successfully retrieved news',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $newsableType = match ($request->newsable_type) {
            SearchByEnum::Teams->value => Team::class,
            SearchByEnum::Leagues->value => Competition::class,
            SearchByEnum::Players->value => Player::class,
            default => '',
        };

        $path = Storage::disk('public')->put('/news', $request->image);
        $validatedData = array_merge($validatedData, ['image' => $path, 'newsable_type' => $newsableType]);

        $news = News::query()->create($validatedData);

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news created',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        return response()->json([
            'data' => $news->load('newsable'),
            'message' => 'Successfully retrieved news',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $validatedData = $request->validated();

        if($request->hasFile('image')) {
            $path = Storage::disk('public')->put('/news', $request->image);
            $validatedData = array_merge($validatedData, ['image' => $path]);
        }

        $news->update($validatedData);

        if (!$news->wasChanged()) {
            return response()->json([
                'data' => $news,
                'message' => 'News not updated',
            ]);
        }

        return response()->json([
            'data' => $news,
            'message' => 'Successfully news updated',
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

    public function isPublished(Request $request, News $news, bool $isPublished)
    {
        $isPublished
            ? $news->update([
            'is_published' => true,
            'published_at' => now()
        ])
            : $news->update([
            'is_published' => false
        ]);

        if (!$news->wasChanged()) {
            return response()->json([
                'data' => null,
                'message' => 'Error updating news',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => null,
            'message' => 'Successfully news updated',
        ]);
    }

    public function isTop(Request $request, News $news, bool $isTop)
    {
        $news->update(['is_top' => $isTop]);

        if (!$news->wasChanged()) {
            return response()->json([
                'data' => null,
                'message' => 'Error updating news',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => null,
            'message' => 'Successfully news updated',
        ]);
    }
}
