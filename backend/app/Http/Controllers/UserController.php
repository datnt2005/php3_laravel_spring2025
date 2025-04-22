<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->paginate(5);
        return view('admin.users.user_list', compact('users'));
    }

    public function store()
    {
        return view('admin.users.user_create');
    }
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'status' => 'required|string',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('users', 'public');
        }
        //them nguoi dung
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->role = $request->role;
        $user->avatar = $avatarPath;
        $user->status = $request->status;
        $user->save();
        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function delete($id)
    {
        $user = User::find($id);
        if(Auth::id() == $id){
            return redirect()->route('users.index')->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }
        if($user->role == 'admin'){
            return redirect()->route('users.index')->with('error', 'Bạn không thể xóa tài khoản admin!');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('admin.users.user_edit', compact('user'));
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'role' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'status' => 'required|string',
        ]);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status
        ];
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ (nếu có)
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Lưu avatar mới
            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User đã được cập nhật!');
    }

    public function viewRegister()
    {
        return view('user.auth.register');
    }

    public function viewLogin()
    {
        return view('user.auth.login');
    }


    public function account()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
        }
        $user = Auth::user();
        return view('user.auth.account', compact('user'));
    }
    public function updateAccount(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
        }
        $user = User::findOrFail($id);

        // Kiểm tra xem user hiện tại có quyền chỉnh sửa không
        if (Auth::id() !== (int) $id) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền chỉnh sửa tài khoản này!');
        }

        // Xác thực dữ liệu nhập vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        // Chuẩn bị dữ liệu để cập nhật
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // Xử lý avatar nếu có upload
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ (nếu có)
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Lưu avatar mới vào thư mục storage/app/public/users
            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }

        // Cập nhật thông tin user
        $user->update($data);

        return redirect()->route('account')->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function changePassword(Request $request, $id)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
        }
    
        // Lấy thông tin người dùng
        $user = User::findOrFail($id);
    
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'oldPassword' => 'required|string|min:6',
            'newPassword' => 'required|string|min:6',
            'newPasswordConfirm' => 'required|string|same:newPassword',
        ]);
    
        // Kiểm tra mật khẩu cũ
        if (!Hash::check($request->oldPassword, $user->password)) {
            return redirect()->route('account')->with('error', 'Mật khẩu cũ không đúng!');
        }
    
        // Cập nhật mật khẩu mới
        $user->password = bcrypt($request->newPassword);
        $user->save();
    
        // Trả về thông báo thành công
        return redirect()->route('account')->with('success', 'Đổi mật khẩu thành công!');
    }
}
