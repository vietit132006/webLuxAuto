<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        // Tìm kiếm theo tên hoặc email
        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->orderBy('user_id', 'desc')->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.users.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'role' => 'required|in:admin,staff,customer',
            'status' => 'required|boolean'
        ]);

        $data = $request->except('password');
        $data['password'] = Hash::make($request->password); // Mã hóa mật khẩu

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Đã thêm người dùng mới!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            // Bỏ qua check trùng email cho chính user đang sửa (Sử dụng user_id)
            'email' => 'required|email|unique:users,email,' . $id . ',user_id',
            'phone' => 'nullable|string',
            'role' => 'required|in:admin,staff,customer',
            'status' => 'required|boolean'
        ]);

        $data = $request->except('password');

        // Nếu admin có nhập mật khẩu mới thì mới cập nhật, không thì giữ nguyên mật khẩu cũ
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thông tin thành công!');
    }

    public function destroy($id)
    {
        // Chặn không cho Admin tự xóa chính mình đang đăng nhập
        if (Auth::id() == $id) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể tự xóa tài khoản đang đăng nhập!');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng!');
    }
}
