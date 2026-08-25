<?php

namespace App\Http\Controllers\Football;

use App\Enums\FavoriteEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteFavoriteRequest;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Competition;
use App\Models\Favorite;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Models\Team;
use App\Support\ScrapingCountryScope;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller
{
    use JsonResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $favorites = auth()->user()
            ->favorites()
            ->with('favoriteable')
            ->get();

        if (ScrapingCountryScope::shouldApply()) {
            $favorites = $favorites->filter(fn (Favorite $f) => $this->favoriteableInScope($f))->values();
        }

        return $this->jsonResponse(collect(FavoriteResource::collection($favorites)), Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $authUserId = auth()->id();
        $favoriteableType = match ($request->favoriteable_type) {
            FavoriteEnum::LEAGUES->value => Competition::class,
            FavoriteEnum::PLAYERS->value => Player::class,
            FavoriteEnum::TEAMS->value => Team::class,
            FavoriteEnum::MATCHES->value => FootballMatch::class,
        };

        if (ScrapingCountryScope::shouldApply() && ! $this->favoriteableIdAllowed($favoriteableType, (string) $request->favoriteable_id)) {
            return $this->jsonResponse(['message' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        }

        $favorite = Favorite::query()
            ->firstOrCreate([
                'favoriteable_id' => $request->favoriteable_id,
                'favoriteable_type' => $favoriteableType,
                'user_id' => $authUserId,
            ]);

        return $this->jsonResponse(collect($favorite), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteFavoriteRequest $request): JsonResponse
    {
        $favoriteableType = match ($request->favoriteable_type) {
            FavoriteEnum::LEAGUES->value => Competition::class,
            FavoriteEnum::PLAYERS->value => Player::class,
            FavoriteEnum::TEAMS->value => Team::class,
            FavoriteEnum::MATCHES->value => FootballMatch::class,
        };

        $authFavorite = auth()->user()
            ->favorites()
            ->where('favoriteable_type', '=', $favoriteableType)
            ->where('favoriteable_id', '=', $request->favoriteable_id)
            ->first();

        if (! isset($authFavorite)) {
            return $this->jsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        Favorite::query()
            ->where('favoriteable_type', '=', $favoriteableType)
            ->where('favoriteable_id', '=', $request->favoriteable_id)
            ->delete();

        return $this->jsonResponse([], Response::HTTP_OK);
    }

    private function favoriteableInScope(Favorite $favorite): bool
    {
        $model = $favorite->favoriteable;
        if ($model === null) {
            return false;
        }

        return match (true) {
            $model instanceof Competition => ScrapingCountryScope::competitionIdAllowed($model->competition_id),
            $model instanceof FootballMatch => ScrapingCountryScope::competitionIdAllowed($model->competition_id),
            $model instanceof Team => ScrapingCountryScope::teamIdAllowed($model->team_id),
            $model instanceof Player => ScrapingCountryScope::playerIdAllowed($model->player_id),
            default => true,
        };
    }

    private function favoriteableIdAllowed(string $favoriteableType, string $id): bool
    {
        return match ($favoriteableType) {
            Competition::class => ScrapingCountryScope::competitionIdAllowed($id),
            FootballMatch::class => ScrapingCountryScope::competitionIdAllowed(
                FootballMatch::query()->where('match_id', $id)->value('competition_id')
            ),
            Team::class => ScrapingCountryScope::teamIdAllowed($id),
            Player::class => ScrapingCountryScope::playerIdAllowed($id),
            default => true,
        };
    }
}
