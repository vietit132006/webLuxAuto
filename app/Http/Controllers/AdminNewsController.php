<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminNewsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status'),
            'author_id' => $request->input('author_id'),
            'featured' => $request->input('featured'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $query = News::query()
            ->with(['category', 'author:user_id,name,email'])
            ->withCount('tags');

        $this->applyFilters($query, $filters);

        $news = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = NewsCategory::orderBy('sort_order')->orderBy('name')->get();
        $authors = User::query()
            ->whereIn('user_id', News::query()->whereNotNull('author_id')->select('author_id'))
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);

        $stats = [
            'total' => News::count(),
            'published' => News::where('status', News::STATUS_PUBLISHED)->count(),
            'scheduled' => News::where('status', News::STATUS_SCHEDULED)->count(),
            'draft' => News::where('status', News::STATUS_DRAFT)->count(),
        ];

        return view('admin.news.index', [
            'news' => $news,
            'filters' => $filters,
            'categories' => $categories,
            'authors' => $authors,
            'statuses' => News::statuses(),
            'stats' => $stats,
        ]);
    }

    public function show(News $news): View
    {
        $news->load(['category', 'author:user_id,name,email', 'tags', 'relatedBrand', 'relatedModel.brand', 'relatedCar.carModel.brand']);

        return view('admin.news.show', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->resolveSlug($request);
        $data['content'] = $this->sanitizeHtml($data['content']);
        $data['reading_time'] = $this->estimateReadingTime($data['content']);
        $data['author_id'] = $data['author_id'] ?? $request->user()?->user_id;

        $this->normalizePublishDates($data);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('news/thumbnails', 'public');
        }

        $tags = $request->input('tags', '');
        $news = News::create($data);
        $this->syncTags($news, $tags);

        return redirect()
            ->route('admin.news.show', $news)
            ->with('success', 'Đã tạo bài viết tin tức.');
    }

    public function edit(News $news): View
    {
        $news->load('tags');

        return view('admin.news.form', $this->formData($news));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $data = $this->validatedData($request, $news);
        $data['slug'] = $this->resolveSlug($request, $news);
        $data['content'] = $this->sanitizeHtml($data['content']);
        $data['reading_time'] = $this->estimateReadingTime($data['content']);

        $this->normalizePublishDates($data);

        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnail($news->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('news/thumbnails', 'public');
        }

        $news->update($data);
        $this->syncTags($news, $request->input('tags', ''));

        return redirect()
            ->route('admin.news.show', $news)
            ->with('success', 'Đã cập nhật bài viết tin tức.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->deleteThumbnail($news->thumbnail);
        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Đã xóa bài viết tin tức.');
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
            $keyword = $filters['q'];
            $query->where(function (Builder $searchQuery) use ($keyword): void {
                $searchQuery->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        });

        $query->when($filters['category_id'], fn (Builder $query, $value) => $query->where('category_id', $value));
        $query->when($filters['status'], fn (Builder $query, $value) => $query->where('status', $value));
        $query->when($filters['author_id'], fn (Builder $query, $value) => $query->where('author_id', $value));

        if ($filters['featured'] !== null && $filters['featured'] !== '') {
            $query->where('is_featured', (bool) $filters['featured']);
        }

        $query->when($filters['from'], fn (Builder $query, $value) => $query->whereDate('created_at', '>=', $value));
        $query->when($filters['to'], fn (Builder $query, $value) => $query->whereDate('created_at', '<=', $value));
    }

    private function formData(?News $news = null): array
    {
        return [
            'news' => $news,
            'statuses' => News::statuses(),
            'ctaTypes' => News::ctaTypes(),
            'categories' => NewsCategory::orderBy('sort_order')->orderBy('name')->get(),
            'authors' => User::query()
                ->whereIn('role', ['admin', 'staff'])
                ->orderBy('name')
                ->get(['user_id', 'name', 'email']),
            'brands' => Brand::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['brand_id', 'name']),
            'models' => CarModel::query()
                ->with('brand:brand_id,name')
                ->orderBy('name')
                ->get(),
            'cars' => Car::query()
                ->with(['carModel.brand', 'modelInfo.brand'])
                ->orderByDesc('created_at')
                ->limit(300)
                ->get(['car_id', 'car_model_id', 'name', 'price', 'image', 'status', 'stock', 'stock_quantity', 'reserved_quantity']),
        ];
    }

    private function validatedData(Request $request, ?News $news = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', Rule::exists('news_categories', 'id')],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'user_id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'thumbnail_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(News::statuses()))],
            'scheduled_at' => ['nullable', 'date', 'required_if:status,' . News::STATUS_SCHEDULED],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'related_brand_id' => ['nullable', 'integer', Rule::exists('brands', 'brand_id')],
            'related_model_id' => ['nullable', 'integer', Rule::exists('car_models', 'id')],
            'related_car_id' => ['nullable', 'integer', Rule::exists('cars', 'car_id')],
            'cta_type' => ['nullable', Rule::in(array_keys(News::ctaTypes()))],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ]);

        $requestedStatus = $data['status'] ?? News::STATUS_DRAFT;
        if (in_array($requestedStatus, [News::STATUS_PUBLISHED, News::STATUS_SCHEDULED], true)
            && !$request->user()?->can('news.publish')) {
            abort(403, 'Bạn chưa có quyền xuất bản tin tức.');
        }

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['cta_type'] = $data['cta_type'] ?: News::CTA_NONE;

        if ($data['cta_type'] === News::CTA_NONE) {
            $data['cta_label'] = null;
            $data['cta_url'] = null;
        }

        unset($data['tags'], $data['thumbnail']);

        return $data;
    }

    private function resolveSlug(Request $request, ?News $news = null): string
    {
        $hasCustomSlug = $request->filled('slug');
        $base = $hasCustomSlug
            ? (string) $request->input('slug')
            : ($news?->slug ?: (string) $request->input('title'));

        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = Str::slug((string) $request->input('title')) ?: 'tin-tuc';
        }

        $conflictQuery = News::where('slug', $slug);
        if ($news) {
            $conflictQuery->whereKeyNot($news->id);
        }

        if ($hasCustomSlug && $conflictQuery->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Slug này đã được sử dụng. Vui lòng chọn slug khác.',
            ]);
        }

        if ($hasCustomSlug) {
            return $slug;
        }

        $baseSlug = $slug;
        $counter = 2;
        while ($conflictQuery->exists()) {
            $slug = $baseSlug . '-' . $counter++;
            $conflictQuery = News::where('slug', $slug);
            if ($news) {
                $conflictQuery->whereKeyNot($news->id);
            }
        }

        return $slug;
    }

    private function normalizePublishDates(array &$data): void
    {
        if ($data['status'] === News::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] !== News::STATUS_SCHEDULED) {
            $data['scheduled_at'] = null;
        }
    }

    private function syncTags(News $news, ?string $rawTags): void
    {
        $tagIds = collect(explode(',', (string) $rawTags))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => Str::slug($tag))
            ->map(function (string $tag): ?int {
                $slug = Str::slug($tag);

                if ($slug === '') {
                    return null;
                }

                return NewsTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tag]
                )->id;
            })
            ->filter()
            ->values()
            ->all();

        $news->tags()->sync($tagIds);
    }

    private function estimateReadingTime(string $content): int
    {
        preg_match_all('/[\p{L}\p{N}]+/u', strip_tags($content), $matches);
        $wordCount = count($matches[0] ?? []);

        return max(1, (int) ceil($wordCount / 220));
    }

    private function deleteThumbnail(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $path = preg_replace('#^/?storage/#', '', $path);
        Storage::disk('public')->delete($path);
    }

    private function sanitizeHtml(?string $html): string
    {
        $html = preg_replace('/<\s*(script|style|iframe|object|embed)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', (string) $html) ?? '';

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $container = $document->getElementsByTagName('div')->item(0);
        if (!$container) {
            return trim(strip_tags($html));
        }

        $allowed = [
            'p' => [],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'a' => ['href', 'title', 'target', 'rel'],
            'blockquote' => [],
            'h2' => [],
            'h3' => [],
            'h4' => [],
            'h5' => [],
            'h6' => [],
            'hr' => [],
        ];

        $this->sanitizeNode($container, $allowed);

        $clean = '';
        foreach (iterator_to_array($container->childNodes) as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    private function sanitizeNode(DOMNode $node, array $allowed): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (!array_key_exists($tag, $allowed)) {
                $fragment = $node->ownerDocument->createDocumentFragment();
                while ($child->firstChild) {
                    $fragment->appendChild($child->firstChild);
                }
                $node->replaceChild($fragment, $child);
                $this->sanitizeNode($node, $allowed);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim($attribute->value);

                if (str_starts_with($name, 'on') || !in_array($name, $allowed[$tag], true)) {
                    $child->removeAttribute($attribute->name);
                    continue;
                }

                if ($tag === 'a' && $name === 'href' && !$this->isSafeHref($value)) {
                    $child->removeAttribute('href');
                }
            }

            if ($tag === 'a') {
                $child->setAttribute('rel', 'noopener noreferrer');
                $child->setAttribute('target', '_blank');
            }

            $this->sanitizeNode($child, $allowed);
        }
    }

    private function isSafeHref(string $href): bool
    {
        return str_starts_with($href, '#')
            || str_starts_with($href, '/')
            || preg_match('/^(https?:|mailto:|tel:)/i', $href) === 1;
    }
}
