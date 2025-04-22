<!DOCTYPE html>
<html>
<head>
    <title>TDAJTSHOP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color:rgb(13, 104, 240);
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }
        .email-body h2 {
            color: rgb(13, 104, 240);
            font-size: 20px;
            margin-bottom: 10px;
        }
        .email-body p {
            margin: 10px 0;
        }
        .email-footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #666666;
        }
        .email-footer a {
            color:rgb(3, 115, 219);
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>TDAJTSHOP</h1>
        </div>
        <div class="email-body">
            <h2>Xin chào Thượng Đế {{ $order->user->name }},</h2>
            <p>Bạn vừa đặt đơn hàng <strong>#{{ $order->id }}</strong> thành công tại <strong>TDAJTSHOP</strong>.</p>
            <p><strong>Tổng tiền:</strong> {{ number_format($order->final_price) }} VNĐ</p>
            <p>Chúng tôi sẽ sớm xử lý đơn hàng và liên hệ giao hàng.</p>
            <p>Cảm ơn bạn đã mua hàng!</p>
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p><a href="{{ url('/') }}">Truy cập cửa hàng của chúng tôi</a></p>
        </div>
    </div>
</body>
</html>