<?php

namespace App\Http\Controllers\Dashboard\FooterMatchLinks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFooterMatchLinkRequest;
use App\Http\Requests\Dashboard\UpdateFooterMatchLinkRequest;
use App\Models\FooterMatchLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FooterMatchLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:footer.match-links.view')->only(['index', 'show']);
        $this->middleware('permission:footer.match-links.create')->only(['store']);
        $this->middleware('permission:footer.match-links.update')->only(['update']);
        $this->middleware('permission:footer.match-links.delete')->only(['destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $links = FooterMatchLink::query()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('url', 'like', "%{$q}%"))
            ->orderBy('column')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        if ($links->total() === 0) {
            return response()->json([
                'data' => null,
                'message' => 'No data found',
            ], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'data' => $links,
            'message' => 'Successfully retrieved footer match links',
        ]);
    }

    public function store(StoreFooterMatchLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['open_in_new_tab'] = $validated['open_in_new_tab'] ?? false;

        $link = FooterMatchLink::query()->create($validated);

        return response()->json([
            'data' => $link,
            'message' => 'Successfully footer match link created',
        ], Response::HTTP_CREATED);
    }

    public function show(FooterMatchLink $footerMatchLink): JsonResponse
    {
        return response()->json([
            'data' => $footerMatchLink,
            'message' => 'Successfully retrieved footer match link',
        ]);
    }

    public function update(UpdateFooterMatchLinkRequest $request, FooterMatchLink $footerMatchLink): JsonResponse
    {
        $footerMatchLink->update($request->validated());

        if (!$footerMatchLink->wasChanged()) {
            return response()->json([
                'data' => $footerMatchLink,
                'message' => 'Footer match link not updated',
            ]);
        }

        return response()->json([
            'data' => $footerMatchLink,
            'message' => 'Successfully footer match link updated',
        ]);
    }

    public function destroy(FooterMatchLink $footerMatchLink): JsonResponse
    {
        $footerMatchLink->delete();

        return response()->json([
            'data' => null,
            'message' => 'Successfully footer match link deleted',
        ]);
    }
}
