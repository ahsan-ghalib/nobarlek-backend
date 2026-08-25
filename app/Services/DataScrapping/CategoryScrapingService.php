<?php

namespace App\Services\DataScrapping;

use App\Models\Category;
use App\Traits\FootballScoreApiTrait;
use Carbon\Carbon;

class CategoryScrapingService
{
    use FootballScoreApiTrait;

    /**
     * Scrap and inset/update the Categories from the sports api.
     */
    public function scrapCategoryData(array $params = []): void
    {
        $categoriesData = $this->callApi('/football/category/list', $params);

        try {
            $categoriesData = collect($categoriesData['results'] ?? [])
                ->transform(function ($category) {
                    return [
                        'category_id' => $category['id'],
                        'name' => $category['name'],
                        'updated_at' => Carbon::parse($category['updated_at']),
                    ];
                })
                ->toArray();

            Category::query()
                ->upsert($categoriesData, ['category_id'], ['name', 'updated_at']);

        } catch (\Exception $exception) {
            // TODO: trigger the notification

            info('/football/category/list: '.$exception->getMessage());
        }

    }
}
