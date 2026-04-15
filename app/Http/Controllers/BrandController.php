<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // 1. Danh sách hãng xe
    public function index()
    {
        $brands = Brand::orderBy('brand_id', 'desc')->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    // 2. Trang thêm mới
    public function create()
    {
        return view('admin.brands.form');
    }

    // 3. Xử lý lưu thêm mới
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Brand::create($request->only('name', 'country'));

        return redirect()->route('admin.brands.index')->with('success', 'Đã thêm hãng xe thành công!');
    }

    // 4. Trang sửa
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.form', compact('brand'));
    }

    // 5. Xử lý cập nhật
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $brand = Brand::findOrFail($id);
        $brand->update($request->only('name', 'country'));

        return redirect()->route('admin.brands.index')->with('success', 'Đã cập nhật hãng xe!');
    }

    // 6. Xử lý xóa
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Đã xóa hãng xe!');
    }
}
