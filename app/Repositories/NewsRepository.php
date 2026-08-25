<?php

namespace App\Repositories;

use App\Models\News;
use App\Repositories\Interfaces\NewsInterface;
use Illuminate\Database\Eloquent\Model;

class NewsRepository implements NewsInterface
{
    private News $model;

    public function __construct()
    {
        $this->model = new News();
    }

    public function newsQuery($select = [])
    {
        return $this->model
            ->query()
            ->select(['id', 'slug', 'title', 'image', 'description', 'is_published', 'is_top', 'published_at', 'created_at'])
            ->latest();
    }

    public function fetchNews()
    {
        return $this->newsQuery();
    }

    public function fetchTopNews()
    {
        return $this->newsQuery()
            ->isPublished()
            ->where('is_top', '=', true)
            ->limit(10)
            ->get();
    }



    public function fetchMostReadNews()
    {
        return $this->newsQuery()
            ->where('read_counts', '>', 0)
            ->limit(10)
            ->latest('read_counts')
            ->get();
    }

    public function fetchRelatedNews(array $keywords, int $id)
    {
        return $this->newsQuery()
            ->whereNot('id', '=', $id)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->when(strlen($keyword) > 0, fn($query) => $query->orWhereRaw("INSTR(meta_keywords, ?) > 0", [$keyword]));
                }
            })
            ->limit(10)
            ->get();
    }
}
