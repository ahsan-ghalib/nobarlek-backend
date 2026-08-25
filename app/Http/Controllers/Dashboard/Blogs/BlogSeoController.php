<?php

namespace App\Http\Controllers\Dashboard\Blogs;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBlogSeoRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogSeoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateBlogSeoRequest $request, Blog $blog)
    {
        $blog->update($request->validated());

        if (!$blog->wasChanged()) {
            return response()->json([
                'data' => $blog,
                'message' => 'Error updating blog',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $blog,
            'message' => 'Successfully blog updated',
        ]);
    }
}
