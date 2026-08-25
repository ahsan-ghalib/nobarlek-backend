<?php

namespace App\Http\Controllers;

use App\Models\FooterMatchLink;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FooterMatchLinkController extends Controller
{
    public function __invoke(Request $request)
    {
        $links = FooterMatchLink::query()
            ->where('is_active', true)
            ->orderBy('column')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'title', 'url', 'column', 'open_in_new_tab']);

        $columns = array_fill(0, 5, []);

        foreach ($links as $link) {
            $index = max(0, min(4, (int) $link->column - 1));
            $columns[$index][] = [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'open_in_new_tab' => (bool) $link->open_in_new_tab,
            ];
        }

        return $this->jsonResponse(['columns' => $columns], Response::HTTP_OK);
    }
}
