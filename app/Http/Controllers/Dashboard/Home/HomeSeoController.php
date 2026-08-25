<?php

namespace App\Http\Controllers\Dashboard\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateHomeSeoRequest;
use App\Models\HomeSeo;
use Symfony\Component\HttpFoundation\Response;

class HomeSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:seo.home');
    }

    public function show()
    {
        $seo = HomeSeo::query()->first();
        if (!$seo) {
            $seo = HomeSeo::query()->create([
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
                'seo_level' => null,
            ]);
        }

        return response()->json($seo);
    }

    public function update(UpdateHomeSeoRequest $request)
    {
        $seo = HomeSeo::query()->first();
        if (!$seo) {
            $seo = HomeSeo::query()->create([]);
        }

        $seo->update($request->validated());

        if (!$seo->wasChanged()) {
            return response()->json([
                'data' => $seo,
                'message' => 'Homepage SEO not updated',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => $seo,
            'message' => 'Successfully homepage SEO updated',
        ]);
    }
}

