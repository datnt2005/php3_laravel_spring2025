<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

return [
    'vnpay_url' => env('VNPAY_API_URL'), // URL của VNPay
    'vnpay_merchant_code' => env('VNPAY_MERCHANT_CODE'),  // mã website (TmnCode) của bạn từ VNPay
    'vnpay_hash_secret' => env('VNPAY_SECRET_KEY'), // mã bí mật (chuỗi ký tự do VNPay cung cấp)
    'vnpay_return_url' => env('APP_URL') . env('VNPAY_RETURN_URL'), // URL trả về sau khi thanh toán thành công
    'vnpay_notify_url' => env('APP_URL') . env('VNPAY_NOTIFY_URL'), // URL thông báo kết quả thanh toán
];
