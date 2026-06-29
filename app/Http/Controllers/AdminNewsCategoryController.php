<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminNewsCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        $categories = NewsCategory::query()
            ->withCount('news')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.news_categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.news_categories.form', [
            'category' => new NewsCategory(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name']);

        NewsCategory::create($data);

        return redirect()
            ->route('admin.news-categories.index')
            ->with('success', 'Đã tạo chuyên mục tin tức.');
    }

    public function edit(NewsCategory $newsCategory): View
    {
        return view('admin.news_categories.form', ['category' => $newsCategory]);
    }

    public function update(Request $request, NewsCategory $newsCategory): RedirectResponse
    {
        $data = $this->validatedData($request, $newsCategory);
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name'], $newsCategory);

        $newsCategory->update($data);

        return redirect()
            ->route('admin.news-categories.index')
            ->with('success', 'Đã cập nhật chuyên mục tin tức.');
    }

    public function toggleStatus(NewsCategory $newsCategory): RedirectResponse
    {
        $newsCategory->update(['is_active' => !$newsCategory->is_active]);

        return back()->with('success', 'Đã cập nhật trạng thái chuyên mục.');
    }

    public function destroy(NewsCategory $newsCategory): RedirectResponse
    {
        if ($newsCategory->news()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Không thể xóa chuyên mục đang có bài viết. Hãy chuyển bài viết sang chuyên mục khác trước.',
            ]);
        }

        $newsCategory->delete();

        return redirect()
            ->route('admin.news-categories.index')
            ->with('success', 'Đã xóa chuyên mục tin tức.');
    }

    private function validatedData(Request $request, ?NewsCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('news_categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]) + [
            'is_active' => false,
            'sort_order' => 0,
        ];
    }

    private function resolveSlug(?string $slug, string $name, ?NewsCategory $category = null): string
    {
        $slug = Str::slug($slug ?: $name);
        if ($slug === '') {
            $slug = 'chuyen-muc';
        }

        $baseSlug = $slug;
        $counter = 2;
        $query = NewsCategory::where('slug', $slug);
        if ($category) {
            $query->whereKeyNot($category->id);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter++;
            $query = NewsCategory::where('slug', $slug);
            if ($category) {
                $query->whereKeyNot($category->id);
            }
        }

        return $slug;
    }
}
