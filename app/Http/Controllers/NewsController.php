<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // Trang danh sách tin tức (Có tìm kiếm)
    public function index(Request $request)
    {
        $search = $request->input('q');

        $news = News::where('status', 1) // Chỉ lấy bài viết đang Đã xuất bản
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('client.news.index', compact('news', 'search'));
    }

    // Trang đọc chi tiết 1 bài viết
    public function show($slug)
    {
        // Tìm bài viết theo đường dẫn (slug)
        $article = News::where('slug', $slug)->where('status', 1)->firstOrFail();

        // Lấy thêm 3 bài viết mới nhất để làm tin liên quan
        $relatedNews = News::where('status', 1)->where('news_id', '!=', $article->news_id)->latest()->take(3)->get();

        return view('client.news.show', compact('article', 'relatedNews'));
    }
}
