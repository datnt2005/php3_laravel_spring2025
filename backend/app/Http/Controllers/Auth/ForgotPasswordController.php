<?php

namespace App\Http\Controllers\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

use function Termwind\render;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;


    public function index()
    {
        return view('user.auth.passwords.forgotPassword');
    }
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            User::where('otp', $request->email)->delete();
            $otp = rand(100000, 999999);
            $otp_expired_at = now()->addMinutes(10);
            $user->otp = $otp;
            $user->otp_expired_at = $otp_expired_at;
            $user->save();
            $details = [
                'otp' => $otp,
            ];
            Mail::to($request->email)->send(new OtpMail($details));
            return redirect()->route('reset-password.index', ['email' => $request->email])
                             ->with('success', 'OTP đã được gửi, hãy kiểm tra email của bạn!');
        } else {
            return redirect()->back()->with('error', 'Email không chính xác');
        }
    }
}
