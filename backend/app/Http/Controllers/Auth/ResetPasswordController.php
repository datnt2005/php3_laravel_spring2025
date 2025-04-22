<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Models\User;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{

    use ResetsPasswords;
    public function index(Request $request){
        $email = $request->input('email');
        return view('user.auth.passwords.resetPassword', ['email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
            'password' => 'required|string|min:6',
            'passwordConfirm' => 'required|string|min:6|same:password',
        ]);
    
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return redirect()->back()->with('error', 'Email không chính xác');
        }
    
        if ($user->otp != $request->otp) {
            return redirect()->back()->with('error', 'OTP không chính xác');
        }
    
        $user->password = bcrypt($request->password);
        $user->otp = null; 
        $user->save();
    
        return redirect()->route('login')->with('success', 'Mật khẩu đã được cập nhật!');
    }
    
}
