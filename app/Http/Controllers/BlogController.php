<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\TeamStanding;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::query()
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
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        $blog->load([
            'footballMatch:id,kickoff_time,match_time,home_team_id,away_team_id,status_id,match_id,competition_id' => [
                'homeTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'awayTeam:team_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo',
                'competition:competition_id,name'
            ]
        ]);

        return response()->json([
            'data' => $blog,
            'message' => 'Successfully retrieved blogs',
        ]);
    }
}
