<?php

return [
    'required' => ':attribute không được để trống.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'min' => [
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'max' => [
        'string' => ':attribute không được vượt quá :max ký tự.',
    ],
    'numeric' => ':attribute phải là số.',
    'confirmed' => ':attribute không khớp.',
    
    'attributes' => [
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'passwordConfirm' => 'Xác nhận mật khẩu',
        'otp' => 'Mã OTP',
    ],
];
