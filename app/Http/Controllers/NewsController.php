<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'category' => $request->input('category'),
        ];

        $baseQuery = $this->visibleNewsQuery();

        $featuredNews = (clone $baseQuery)
            ->where('is_featured', true)
            ->publishedFirst()
            ->take(4)
            ->get();

        $newsQuery = (clone $baseQuery)
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $keyword = $filters['q'];
                $query->where(function (Builder $searchQuery) use ($keyword): void {
                    $searchQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhere('summary', 'like', "%{$keyword}%")
                        ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->when($filters['category'], function (Builder $query, string $slug): void {
                $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $slug));
            });

        $news = $newsQuery
            ->publishedFirst()
            ->paginate(9)
            ->withQueryString();

        $categories = NewsCategory::query()
            ->active()
            ->withCount(['news' => fn (Builder $query) => $query->visible()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $latestNews = (clone $baseQuery)->publishedFirst()->take(5)->get();
        $popularNews = (clone $baseQuery)->orderByDesc('views_count')->publishedFirst()->take(5)->get();

        return view('client.news.index', [
            'news' => $news,
            'filters' => $filters,
            'categories' => $categories,
            'featuredNews' => $featuredNews,
            'latestNews' => $latestNews,
            'popularNews' => $popularNews,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $article = $this->visibleNewsQuery()
            ->where('slug', $slug)
            ->firstOrFail();

        $sessionKey = 'news_viewed_' . $article->id;
        if (!$request->session()->has($sessionKey)) {
            $article->increment('views_count');
            $request->session()->put($sessionKey, true);
            $article->refresh();
        }

        $relatedNews = $this->visibleNewsQuery()
            ->whereKeyNot($article->id)
            ->when($article->category_id, fn (Builder $query) => $query->where('category_id', $article->category_id))
            ->publishedFirst()
            ->take(3)
            ->get();

        if ($relatedNews->count() < 3) {
            $fallback = $this->visibleNewsQuery()
                ->whereKeyNot($article->id)
                ->whereNotIn('id', $relatedNews->pluck('id'))
                ->publishedFirst()
                ->take(3 - $relatedNews->count())
                ->get();

            $relatedNews = $relatedNews->concat($fallback);
        }

        $relatedCars = $this->relatedCars($article);
        $latestNews = $this->visibleNewsQuery()->whereKeyNot($article->id)->publishedFirst()->take(5)->get();
        $popularNews = $this->visibleNewsQuery()->whereKeyNot($article->id)->orderByDesc('views_count')->take(5)->get();

        return view('client.news.show', compact(
            'article',
            'relatedNews',
            'relatedCars',
            'latestNews',
            'popularNews'
        ));
    }

    private function visibleNewsQuery(): Builder
    {
        return News::query()
            ->visible()
            ->with(['category', 'author:user_id,name', 'tags', 'relatedCar.carModel.brand', 'relatedBrand', 'relatedModel.brand'])
            ->where(function (Builder $query): void {
                $query->whereNull('category_id')
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->active());
            });
    }

    private function relatedCars(News $article)
    {
        $query = Car::query()
            ->with(['carModel.brand', 'modelInfo.brand', 'images'])
            ->withActiveBrand();

        if ($article->related_car_id) {
            return $query->where('car_id', $article->related_car_id)->take(1)->get();
        }

        if ($article->related_model_id) {
            return $query->where('car_model_id', $article->related_model_id)
                ->availableForSale()
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->take(3)
                ->get();
        }

        if ($article->related_brand_id) {
            return $query->whereHas('carModel.brand', function (Builder $brandQuery) use ($article): void {
                $brandQuery->where('brand_id', $article->related_brand_id);
            })
                ->availableForSale()
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->take(3)
                ->get();
        }

        return collect();
    }
}
