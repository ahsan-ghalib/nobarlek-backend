<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ScrapingCountryScope;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke()
    {
        $categories = Category::query()
            ->when(ScrapingCountryScope::shouldApply(), function (Builder $q) {
                $q->whereHas('countries.competitions', function (Builder $cq) {
                    ScrapingCountryScope::restrictCompetitionsQuery($cq);
                });
            })
            ->get();

        if ($categories->count() === 0) {
            return response()->json([
                'message' => 'No data found',
                'data' => null,
            ], SymfonyResponse::HTTP_NO_CONTENT);
        }

        return response()->json([
            'message' => 'Successfully retrieved categories',
            'data' => $categories,
        ], SymfonyResponse::HTTP_NO_CONTENT);
    }
}
