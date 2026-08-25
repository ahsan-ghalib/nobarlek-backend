<?php

namespace App\Http\Controllers\Dashboard\Blogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = trim((string) request()->query('q', ''));

        $blogs = Blog::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10);

        if ($blogs->total() === 0) {
            return response()->json([
                'data' => null,
                'message' => 'No data found',
            ], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'data' => $blogs,
            'message' => 'Successfully retrieved blogs',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlogRequest $request)
    {
        $path = Storage::disk('public')->put('/blogs', $request->image);
        $validatedData = array_merge($request->validated(), ['image' => $path]);

        $blog = Blog::query()
            ->create($validatedData);

        return response()->json([
            'data' => $blog,
            'message' => 'Successfully blog created',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        $blog->load([
            'footballMatch:id,kickoff_time,match_time,home_team_id,away_team_id,status_id' => [
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr',
            ]
        ]);

        return response()->json([
            'data' => $blog,
            'message' => 'Success fetching blog',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        $validatedData = $request->validated();

        if($request->hasFile('image')) {
            $path = Storage::disk('public')->put('/blogs', $request->image);
            $validatedData = array_merge($validatedData, ['image' => $path]);
        }

        $blog->update($validatedData);

        if (!$blog->wasChanged()) {
            return response()->json([
                'data' => $blog,
                'message' => 'Blog not updated',
            ]);
        }

        return response()->json([
            'data' => $blog,
            'message' => 'Successfully blog updated',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();

        return response()->json([
            'data' => $blog,
            'message' => 'Successfully blog deleted',
        ]);
    }

    public function isPublished(Request $request, Blog $blog, bool $isPublished)
    {
        $isPublished
            ? $blog->update([
            'is_published' => true,
            'published_at' => now()
        ])
            : $blog->update([
            'is_published' => false
        ]);

        if (!$blog->wasChanged()) {
            return response()->json([
                'data' => null,
                'message' => 'Error updating blog',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => null,
            'message' => 'Successfully blog updated',
        ]);
    }
}

