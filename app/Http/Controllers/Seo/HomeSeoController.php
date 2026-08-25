<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\HomeSeo;
use Symfony\Component\HttpFoundation\Response;

class HomeSeoController extends Controller
{
    public function __invoke()
    {
        $seo = HomeSeo::query()->first();
        if (!$seo) {
            return $this->jsonResponse(collect([
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => [],
                'seo_level' => null,
            ]), Response::HTTP_OK);
        }

        $seo->append(['meta_keywords']);

        return $this->jsonResponse(collect($seo), Response::HTTP_OK);
    }
}

