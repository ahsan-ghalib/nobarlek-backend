<?php

namespace App\Services\DataScrapping;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Player;
use App\Models\Team;
use App\Traits\FootballScoreApiTrait;

class LanguageDataScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Categories from the sports api.
     */
    public function scrapLanguageData(array $params = []): array
    {
        $languagesData = $this->callApi('/football/language/list', $params);

        $model = '';
        $column = '';

        switch ($params['type']) {
            case 1:
                $model = Category::query();
                $column = 'category_id';
                break;
            case 2:
                $model = Country::query();
                $column = 'country_id';
                break;
            case 3:
                $model = Competition::query();
                $column = 'competition_id';
                break;
            case 4:
                $model = Team::query();
                $column = 'team_id';
                break;
            case 5:
                $model = Player::query();
                $column = 'player_id';
                break;
            default:
                break;
        }

        try {
            collect($languagesData['results'] ?? [])
                ->map(fn ($language) => (clone $model)->where($column, '=', $language['id'])
                    ->where('name', '=', $language['name_en'])
                    ->update([
                        'name_aa' => $language['name_aa'],
                        'name_nl' => $language['name_nl'],
                        'name_pt' => $language['name_pt'],
                        'name_br' => $language['name_br'],
                        'name_es' => $language['name_es'],
                        'name_fr' => $language['name_fr'],
                        'name_de' => $language['name_de'],
                        'name_vi' => $language['name_vi'],
                    ]));

            return $languagesData['query'];

        } catch (\Exception $exception) {
            // TODO: trigger the notification
            info('/football/language/list: '.$exception->getMessage());

            return $languagesData['errors'];
        }
    }
}
