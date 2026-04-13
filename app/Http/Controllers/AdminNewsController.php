<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Dùng để tự tạo link slug không dấu

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $news = News::when($search, function ($query, $search) {
            return $query->where('title', 'like', "%{$search}%");
        })->orderBy('news_id', 'desc')->paginate(10);

        return view('admin.news.index', compact('news', 'search'));
    }

    public function create()
    {
        return view('admin.news.form');
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|max:255', 'content' => 'required']);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title); // Tự động tạo link từ tiêu đề

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        News::create($data);
        return redirect()->route('admin.news.index')->with('success', 'Đã thêm bài viết mới!');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.form', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required|max:255', 'content' => 'required']);
        $news = News::findOrFail($id);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($data);
        return redirect()->route('admin.news.index')->with('success', 'Đã cập nhật bài viết!');
    }

    public function destroy($id)
    {
        News::findOrFail($id)->delete();
        return redirect()->route('admin.news.index')->with('success', 'Đã xóa bài viết!');
    }
}
