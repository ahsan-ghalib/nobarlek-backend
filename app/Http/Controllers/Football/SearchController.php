<?php

namespace App\Http\Controllers\Football;

use App\Enums\SearchByEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Competition;
use App\Models\Player;
use App\Models\Team;
use App\Support\ScrapingCountryScope;
use App\Traits\JsonResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    use JsonResponseTrait;

    private string $keyword;

    /**
     * Handle the incoming request.
     */
    public function __invoke(SearchRequest $request)
    {
        $this->keyword = $request->keyword;

        $data = match ($request->search_by) {
            SearchByEnum::All->value => [
                'Leagues' => $this->searchModel(Competition::query(), ['competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id', 'priority'])->limit(10)->get(),
                'Teams' => $this->searchModel(Team::query(), ['team_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id'])->limit(10)->get(),
                'Players' => $this->searchModel(Player::query(), ['player_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id'])->limit(10)->get(),
            ],
            SearchByEnum::Leagues->value => $this->searchModel(Competition::query(), ['id', 'competition_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id', 'priority'])->paginate(10),
            SearchByEnum::Teams->value => $this->searchModel(Team::query(), ['id', 'team_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id'])->paginate(10),
            SearchByEnum::Players->value => $this->searchModel(Player::query(), ['id', 'player_id', 'name', 'name_aa', 'name_nl', 'name_vi', 'name_pt', 'name_br', 'name_es', 'name_fr', 'name_de', 'logo', 'country_id'])->paginate(10),
        };

        return $this->jsonResponse(collect($data), Response::HTTP_OK);
    }

    public function searchModel($query, array $columns): Builder
    {
        $model = $query->getModel();
        if ($model instanceof Competition) {
            ScrapingCountryScope::restrictCompetitionsQuery($query);
        } elseif ($model instanceof Team) {
            ScrapingCountryScope::restrictTeamsQuery($query);
        } elseif ($model instanceof Player) {
            ScrapingCountryScope::restrictPlayersQuery($query);
        }

        $query = $query->select($columns)
            ->with('country:country_id,name,name_aa,name_nl,name_vi,name_pt,name_br,name_es,name_fr,name_de,logo')
            ->where('name', 'like', '%'.$this->keyword.'%');

        if ($model instanceof Competition) {
            $query->orderedBySequence();
        }

        return $query;
    }
}
