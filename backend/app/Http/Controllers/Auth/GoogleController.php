<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleController extends Controller
{
    // Chuyển hướng đến Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý callback từ Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser) {
                throw new Exception("Không nhận được dữ liệu từ Google");
            }

            Log::info('Google User:', [
                'id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Tạo người dùng mới nếu chưa tồn tại
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt('default_password'), // Mặc định
                ]);
            } else {
                // Cập nhật Google ID nếu chưa có
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }
            Auth::login($user);

            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
        } catch (Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Đăng nhập bằng Google thất bại!');
        }
    }
}
