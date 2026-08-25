<?php

namespace App\Http\Controllers\Football;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Support\ScrapingCountryScope;
use App\Traits\JsonResponseTrait;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CountryController extends Controller
{
    use JsonResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function __invoke()
    {
        $query = Country::query()
            ->withWhereHas('competitions', function ($q) {
                $q->select([
                    'competition_id',
                    'name',
                    'name_aa',
                    'name_nl',
                    'name_vi',
                    'name_pt',
                    'name_br',
                    'name_es',
                    'name_fr',
                    'name_de',
                    'logo',
                    'country_id',
                ]);
                if (ScrapingCountryScope::shouldApply()) {
                    ScrapingCountryScope::restrictCompetitionsQuery($q);
                }
            });

        if (ScrapingCountryScope::shouldApply()) {
            $query->whereIn('country_id', ScrapingCountryScope::resolvedCountryIds());
        } else {
            $query->whereIn('name', [
                'England',
                'Netherlands',
                'France ',
                'Italy',
                'Spain',
                'Belgium',
                'Germany',
                'Turkey',
                'Portugal',
            ]);
        }

        $countries = $query->get();

        return $countries->count() === 0
            ? $this->jsonResponse(collect([]), SymfonyResponse::HTTP_NO_CONTENT)
            : $this->jsonResponse($countries, SymfonyResponse::HTTP_OK);
    }
}
