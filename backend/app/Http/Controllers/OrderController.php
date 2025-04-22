<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\User;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Services\GhnService;
use App\Mail\OrderPlacedMail;
use App\Mail\StatusPlacedMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $status = $request->query('status', 'all');

    $orders = Order::with(['user', 'address'])
        ->when($status !== 'all', function ($query) use ($status) {
            return $query->where('status', $status);
        })
        ->latest()
        ->get();

    return view('admin.orders.order_list', compact('orders', 'status'));
}

    public function changeStatus(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'status' => 'required|in:pending,processing,shipping,completed,cancelled'
    ]);

    $order = Order::findOrFail($request->order_id);
    $orderItems = OrderItem::where('order_id', $order->id)->get();

    // Nếu trạng thái mới là "completed", thì mới trừ số lượng kho
    if ($request->status === 'completed') {
        foreach ($orderItems as $orderItem) {
            $orderItem->productVariant()->decrement('quantityProduct', $orderItem->quantity);
        }
    }

    $order->status = $request->status;
    $order->save();

    // Gửi mail thông báo
    Mail::to($order->user->email)->send(new StatusPlacedMail($order));

    return redirect()->back()->with('success', 'Trạng thái đơn hàng đã được cập nhật!');
}

public function getDetail($id)
{
    // Tìm đơn hàng cùng với thông tin người dùng và địa chỉ
    $order = Order::with(['user', 'address'])->find($id);

    // Nếu đơn hàng không tồn tại, trả về lỗi
    if (!$order) {
        return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
    }

    // Lấy các sản phẩm trong đơn hàng cùng với thông tin chi tiết
    $orderItems = OrderItem::where('order_id', $id)
        ->with(['productVariant.product', 'productVariant.attributes.attribute', 'productVariant.attributes.value'])
        ->get();

    // Xử lý và định dạng thông tin sản phẩm
    $formattedItems = $orderItems->map(function ($item) {
        $variant = $item->productVariant;

        // Kiểm tra nếu biến thể sản phẩm tồn tại
        if (!$variant || !$variant->product) {
            return null;
        }

        return [
            'name' => $variant->product->name,
            'slug' => $variant->product->slug,
            'image' => $variant->image ?? '', // Đảm bảo không lỗi nếu không có ảnh
            'price' => $variant->sale_price ?? $variant->price, // Sử dụng giá khuyến mãi nếu có
            'quantity' => $item->quantity,
            'attributes' => $variant->attributes->map(function ($attribute) {
                return [
                    'name' => $attribute->attribute->name ?? '',
                    'value' => $attribute->value->value ?? '',
                ];
            }),
        ];
    })->filter(); // Loại bỏ các mục null nếu có lỗi

    // Tính tổng số lượng sản phẩm
    $total_quantity = $orderItems->sum('quantity');

    // Chuẩn bị dữ liệu trả về
    return response()->json([
        'status' => 'success',
        'order' => $order,
        'items' => $formattedItems,
        'total_quantity' => $total_quantity
    ]);

}
    public function removeOrder($id)    
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
        }
        $order->delete();
        return response()->json(['success' => 'Đơn hàng đã xoá thành công'], 200);
    }
    
    
    public function showCheckout(Request $request)
    {        
        $addressId = $request->query('address_id');
        $selectedItems = $request->query('selected_items', []);
    
        $address = null;
        if ($addressId) {
            $address = Address::find($addressId);
            if (!$address) {
                return redirect()->back()->with('error', 'Địa chỉ không tồn tại');
            }
        }
    
        $cart = Cart::where('user_id', Auth::id())->first();
    
        if (!$cart) {
            return view('user.cart', [
                'cartItems' => [],
                'cart' => null,
                'totalQuantity' => 0,
                'totalPrice' => 0
            ]);
        }
    
        // Filter cart items based on selected_items
        $cartItemsQuery = CartItem::where('cart_id', $cart->id)->with('productVariant');
        if (!empty($selectedItems)) {
            $cartItemsQuery->whereIn('id', $selectedItems);
        }
        $cartItems = $cartItemsQuery->get();
    
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm để thanh toán');
        }
    
        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price));
    
        return view('user.checkout', compact('cartItems', 'address', 'totalQuantity', 'totalPrice', 'cart'))
            ->with('addresses', Auth::user()->addresses);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code_discount' => 'required|string|max:255',
        ]);
        $totalPrice = $request->input('total_price');
        $coupon = Coupon::where('code', $request->code_discount)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Mã giảm giá không hợp lệ'], 400);
        }

        // Kiểm tra điều kiện áp dụng
        if ($coupon->usage_limit <= 0) {
            return response()->json(['error' => 'Mã giảm giá đã hết'], 400);
        }

        if ($coupon->start_date > now() || $coupon->end_date < now()) {
            return response()->json(['error' => 'Mã giảm giá đã hết hạn sử dụng'], 400);
        }

        if ($coupon->min_order_value > $totalPrice) {
            return response()->json(['error' => 'Đơn hàng phải có giá trị tối thiểu ' . $coupon->min_order_value . 'đ'], 400);
        }

        if($coupon->status == 'inactive') {
            return response()->json(['error' => 'Mã giảm giá không còn hoạt động'], 400);
        }

        $discount_price = 0;
        if ($coupon->discount_type === 'percent') {
            $discount_price = ($coupon->discount_value / 100) * $totalPrice;
        } elseif ($coupon->discount_type === 'fixed') {
            $discount_price = $coupon->discount_value;
        }elseif ($coupon->discount_type === 'free_shipping') {
            $discount_price = 0;
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Mã giảm giá áp dụng thành công',
            'coupon' => $coupon,
            'discount_id' => $coupon->id,
            'discount_price' => $discount_price,
            'final_price' => $totalPrice - $discount_price,
            'total_price' => $totalPrice,
        ]);
    }
    public function selectAddress(Request $request)
    {
        $addressId = $request->input('address_id');
        $address = Address::find($addressId);
    
        if (!$address) {
            return response()->json(['error' => 'Địa chỉ không tồn tại'], 404);
        }
    
        return response()->json(['address' => $address]);
    }

    public function createOrder(Request $request)
{
    $request->validate([
        'address_id' => 'required|exists:addresses,id',
        'note' => 'nullable|string|max:255',
        'payment_method' => 'required|string',
        'total_price' => 'required|numeric',
        'discount_price' => 'nullable|numeric',
        'discount_id' => 'nullable|exists:discounts,id',
        'final_price' => 'nullable|numeric',
        'shipping_fee' => 'nullable|numeric',
        'selected_items' => 'required|array', // Validate selected_items
        'selected_items.*' => 'exists:cart_items,id', // Ensure each item exists in cart_items
    ], [
        'address_id.required' => 'Vui lòng chọn điểm giao hàng',
        'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
        'total_price.required' => 'Vui lòng nhập giá trị đơn hàng',
        'final_price.required' => 'Vui lòng nhập giá trị đơn hàng',
        'selected_items.required' => 'Vui lòng chọn ít nhất một sản phẩm để đặt hàng',
    ]);

    if (!Auth::check()) {
        return response()->json(['error' => 'Bạn cần đăng nhập để đặt hàng'], 401);
    }

    $cart = Cart::where('user_id', Auth::id())->first();
    if (!$cart) {
        return response()->json(['error' => 'Giỏ hàng không tồn tại'], 404);
    }

    // Get only selected cart items
    $selectedItemIds = $request->input('selected_items', []);
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->whereIn('id', $selectedItemIds)
        ->with('productVariant')
        ->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['error' => 'Không có sản phẩm nào được chọn để đặt hàng'], 400);
    }

    // Verify total_price matches the selected items
    $calculatedTotalPrice = $cartItems->sum(function ($item) {
        return $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price);
    });
    if ($calculatedTotalPrice != $request->input('total_price')) {
        return response()->json(['error' => 'Tổng giá trị đơn hàng không hợp lệ'], 400);
    }

    // Handle payment methods
    if ($request->input('payment_method') == 'vnpay') {
        return $this->createPaymentVnpay($request);
    }

    if ($request->input('payment_method') == 'momo') {
        return $this->createPaymentMomo($request);
    }

    // Create the order
    $order = new Order();
    $order->user_id = Auth::id();
    $order->address_id = $request->input('address_id');
    $order->note = $request->input('note');
    $order->payment_method = $request->input('payment_method');
    $order->total_price = $request->input('total_price');
    $order->discount_price = $request->input('discount_price') ?? 0;
    $order->discount_id = $request->input('discount_id') ?? null;
    $order->shipping_fee = $request->input('shipping_fee') ?? 0;
    $order->final_price = $request->input('final_price') ?? ($request->input('total_price') - ($request->input('discount_price') ?? 0));
    $order->status = 'ready_to_pick';
    $order->save();

    // Create order items for selected cart items
    foreach ($cartItems as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->productVariant->product_id,
            'product_variant_id' => $item->productVariant->idVariant,
            'quantity' => $item->quantity,
            'price' => $item->productVariant->sale_price ?? $item->productVariant->price,
        ]);
        ProductVariant::where('idVariant', $item->productVariant->idVariant)->decrement('quantityProduct', $item->quantity);
    }

    // Delete only selected cart items
    CartItem::whereIn('id', $selectedItemIds)->delete();

    // Update discount usage
    $discount = Coupon::find($request->input('discount_id'));
    if ($discount) {
        $discount->usage_limit -= 1;
        $discount->used_count += 1;
        $discount->save();
    }

    // Create GHN order and send email
    app(abstract: GhnService::class)->createGhnOrder($order);
    Mail::to($order->user->email)->send(new OrderPlacedMail($order));

    return redirect()->route('home')->with('success', 'Đặt hàng thành công');
}

public function createPaymentVnpay(Request $request)
{
    // Validate selected_items
    $request->validate([
        'selected_items' => 'required|array',
        'selected_items.*' => 'exists:cart_items,id',
    ], [
        'selected_items.required' => 'Vui lòng chọn ít nhất một sản phẩm để đặt hàng',
    ]);

    // Get selected cart items
    $cart = Cart::where('user_id', Auth::id())->first();
    if (!$cart) {
        return redirect()->route('cart.index')->with('error', 'Giỏ hàng không tồn tại.');
    }

    $selectedItemIds = $request->input('selected_items', []);
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->whereIn('id', $selectedItemIds)
        ->with('productVariant')
        ->get();

    if ($cartItems->isEmpty()) {
        return redirect()->route('cart.index')->with('error', 'Không có sản phẩm nào được chọn để đặt hàng.');
    }

    // Verify total_price
    $calculatedTotalPrice = $cartItems->sum(function ($item) {
        return $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price);
    });
    if ($calculatedTotalPrice != $request->input('total_price')) {
        return redirect()->route('cart.index')->with('error', 'Tổng giá trị đơn hàng không hợp lệ.');
    }

    // Store payment data including selected_items
    session([
        'payment_data' => [
            'address_id' => $request->input('address_id'),
            'note' => $request->input('note'),
            'total_price' => $request->input('total_price'),
            'discount_price' => $request->input('discount_price') ?? 0,
            'discount_id' => $request->input('discount_id') ?? null,
            'final_price' => $request->input('final_price'),
            'shipping_fee' => $request->input('shipping_fee') ?? 0,
            'selected_items' => $selectedItemIds, // Add selected_items
        ]
    ]);

    $vnp_TmnCode = config('vnpay.vnpay_merchant_code');
    $vnp_HashSecret = config('vnpay.vnpay_hash_secret');
    $vnp_Url = config('vnpay.vnpay_url');
    $vnp_Returnurl = config('vnpay.vnpay_return_url');

    $vnp_TxnRef = time(); // Mã đơn hàng duy nhất
    $vnp_OrderInfo = "Thanh toán đơn hàng #" . $vnp_TxnRef;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $request->input('final_price') * 100; // x100
    $vnp_Locale = 'vn';
    $vnp_BankCode = ''; // không cần thiết
    $vnp_IpAddr = $request->ip();

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => now()->format('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    );

    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_Url = $vnp_Url . "?" . $query;
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

    return redirect($vnp_Url);
}

public function return(Request $request)
{
    $inputData = $request->all();
    $vnp_HashSecret = config('vnpay.vnpay_hash_secret');
    $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);

    $hashData = "";
    foreach ($inputData as $key => $value) {
        $hashData .= $key . '=' . urlencode($value) . '&';
    }
    $hashData = rtrim($hashData, '&');
    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    if ($secureHash === $vnp_SecureHash && ($inputData['vnp_ResponseCode'] ?? '') == '00') {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            return redirect()->route('home')->with('error', 'Giỏ hàng không tồn tại.');
        }

        // Get payment data from session
        $data = session('payment_data');
        if (!$data || !isset($data['selected_items'])) {
            return redirect()->route('home')->with('error', 'Không tìm thấy dữ liệu thanh toán.');
        }

        // Get selected cart items
        $selectedItemIds = $data['selected_items'];
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->whereIn('id', $selectedItemIds)
            ->with('productVariant')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', 'Không có sản phẩm nào được chọn để đặt hàng.');
        }

        // Verify total_price
        $calculatedTotalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price);
        });
        if ($calculatedTotalPrice != $data['total_price']) {
            return redirect()->route('home')->with('error', 'Tổng giá trị đơn hàng không hợp lệ.');
        }

        // Create order
        $order = new Order();
        $order->user_id = Auth::id();
        $order->address_id = $data['address_id'];
        $order->note = $data['note'];
        $order->payment_method = 'vnpay';
        $order->total_price = $data['total_price'];
        $order->discount_price = $data['discount_price'];
        $order->discount_id = $data['discount_id'];
        $order->shipping_fee = $data['shipping_fee'] ?? 0;
        $order->final_price = $data['final_price'];
        $order->tracking_code = 'DH' . time() . rand(1000, 9999);
        $order->status = 'ready_to_pick';
        $order->save();

        // Create order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->productVariant->product_id,
                'product_variant_id' => $item->productVariant->idVariant,
                'quantity' => $item->quantity,
                'price' => $item->productVariant->sale_price ?? $item->productVariant->price,
            ]);
            ProductVariant::where('idVariant', $item->productVariant->idVariant)->decrement('quantityProduct', $item->quantity);
        }

        // Delete only selected cart items
        CartItem::whereIn('id', $selectedItemIds)->delete();

        // Update discount usage
        if ($order->discount_id) {
            $coupon = Coupon::find($order->discount_id);
            if ($coupon) {
                $coupon->usage_limit -= 1;
                $coupon->used_count += 1;
                $coupon->save();
            }
        }

        // Create GHN order and send email
        app(abstract: GhnService::class)->createGhnOrder($order);
        Mail::to($order->user->email)->send(new OrderPlacedMail($order));

        // Clear session
        session()->forget('payment_data');

        return redirect()->route('home')->with('success', 'Đặt hàng thành công qua VNPAY!');
    }

    return redirect()->route('home')->with('error', 'Thanh toán thất bại hoặc không hợp lệ.');
}
    
public function createPaymentMomo(Request $request)
{
    // Validate selected_items
    $request->validate([
        'selected_items' => 'required|array',
        'selected_items.*' => 'exists:cart_items,id',
    ], [
        'selected_items.required' => 'Vui lòng chọn ít nhất một sản phẩm để đặt hàng',
    ]);

    // Get selected cart items
    $cart = Cart::where('user_id', Auth::id())->first();
    if (!$cart) {
        return redirect()->route('cart.index')->with('error', 'Giỏ hàng không tồn tại.');
    }

    $selectedItemIds = $request->input('selected_items', []);
    $cartItems = CartItem::where('cart_id', $cart->id)
        ->whereIn('id', $selectedItemIds)
        ->with('productVariant')
        ->get();

    if ($cartItems->isEmpty()) {
        return redirect()->route('cart.index')->with('error', 'Không có sản phẩm nào được chọn để đặt hàng.');
    }

    // Verify total_price
    $calculatedTotalPrice = $cartItems->sum(function ($item) {
        return $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price);
    });
    if ($calculatedTotalPrice != $request->input('total_price')) {
        return redirect()->route('cart.index')->with('error', 'Tổng giá trị đơn hàng không hợp lệ.');
    }

    // Store payment data including selected_items
    session([
        'payment_data' => [
            'address_id' => $request->input('address_id'),
            'note' => $request->input('note'),
            'total_price' => $request->input('total_price'),
            'discount_price' => $request->input('discount_price') ?? 0,
            'discount_id' => $request->input('discount_id') ?? null,
            'final_price' => $request->input('final_price'),
            'shipping_fee' => $request->input('shipping_fee') ?? 0,
            'selected_items' => $selectedItemIds, // Add selected_items
        ]
    ]);

    $endpoint = config('momo.momo_endpoint');
    $partnerCode = config('momo.momo_code');
    $accessKey = config('momo.momo_access_key');
    $secretKey = config('momo.momo_secret_key');

    $orderId = time() . "";
    $requestId = time() . "";
    $amount = $request->input('final_price'); // Giá trị cần thanh toán
    $orderInfo = "Thanh toán đơn hàng #" . $orderId;
    $redirectUrl = config('momo.momo_return_url');
    $ipnUrl = config('momo.momo_notify_url');
    $extraData = ""; 

    // Tạo chữ ký
    $rawHash = "accessKey=" . $accessKey .
        "&amount=" . $amount .
        "&extraData=" . $extraData .
        "&ipnUrl=" . $ipnUrl .
        "&orderId=" . $orderId .
        "&orderInfo=" . $orderInfo .
        "&partnerCode=" . $partnerCode .
        "&redirectUrl=" . $redirectUrl .
        "&requestId=" . $requestId .
        "&requestType=payWithATM";

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    $data = [
        'partnerCode' => $partnerCode,
        'accessKey' => $accessKey,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'extraData' => $extraData,
        'requestType' => "payWithATM",
        'signature' => $signature,
        'lang' => 'vi'
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($result, true);
    if (isset($result['payUrl'])) {
        return redirect($result['payUrl']);
    }

    return redirect()->route('home')->with('error', 'Không thể kết nối tới MoMo!');
}
public function returnMomo(Request $request)
{
    if ($request->get('resultCode') == 0) {
        // Thanh toán thành công
        $data = session('payment_data');
        if (!$data || !isset($data['selected_items'])) {
            return redirect()->route('home')->with('error', 'Không tìm thấy dữ liệu thanh toán.');
        }

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            return redirect()->route('home')->with('error', 'Giỏ hàng không tồn tại.');
        }

        // Get selected cart items
        $selectedItemIds = $data['selected_items'];
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->whereIn('id', $selectedItemIds)
            ->with('productVariant')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('error', 'Không có sản phẩm nào được chọn để đặt hàng.');
        }

        // Verify total_price
        $calculatedTotalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price);
        });
        if ($calculatedTotalPrice != $data['total_price']) {
            return redirect()->route('home')->with('error', 'Tổng giá trị đơn hàng không hợp lệ.');
        }

        // Create order
        $order = new Order();
        $order->user_id = Auth::id();
        $order->address_id = $data['address_id'];
        $order->note = $data['note'];
        $order->payment_method = 'momo';
        $order->total_price = $data['total_price'];
        $order->discount_price = $data['discount_price'];
        $order->discount_id = $data['discount_id'];
        $order->shipping_fee = $data['shipping_fee'] ?? 0;
        $order->final_price = $data['final_price'];
        $order->tracking_code = 'DH' . time() . rand(1000, 9999);
        $order->status = 'ready_to_pick';
        $order->save();

        // Create order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->productVariant->product_id,
                'product_variant_id' => $item->productVariant->idVariant,
                'quantity' => $item->quantity,
                'price' => $item->productVariant->sale_price ?? $item->productVariant->price,
            ]);
            ProductVariant::where('idVariant', $item->productVariant->idVariant)->decrement('quantityProduct', $item->quantity);
        }

        // Delete only selected cart items
        CartItem::whereIn('id', $selectedItemIds)->delete();

        // Update discount usage
        if ($order->discount_id) {
            $coupon = Coupon::find($order->discount_id);
            if ($coupon) {
                $coupon->usage_limit -= 1;
                $coupon->used_count += 1;
                $coupon->save();
            }
        }

        // Create GHN order and send email
        app(abstract: GhnService::class)->createGhnOrder($order);
        Mail::to($order->user->email)->send(new OrderPlacedMail($order));

        // Clear session
        session()->forget('payment_data');

        return redirect()->route('home')->with('success', 'Đặt hàng thành công qua MoMo!');
    }

    return redirect()->route('home')->with('error', 'Thanh toán thất bại qua MoMo!');
}
    public function sendMail(Request $request)
    {
        // Gửi email thông báo đơn hàng thành công
        $order = Order::find($request->input('order_id'));
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
        }

        // Gửi email thông báo đơn hàng thành công
        // Mail::to($order->user->email)->send(new OrderSuccessMail($order));

        return response()->json(['success' => 'Email đã được gửi']);
    }
    
    public function showOrderBuyed(Request $request)
    {
        $orders = User::find(Auth::id())->orders()->get()->sortByDesc('created_at');

        return view('user.order_buyed', compact('orders'));
    }
    public function reorder(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
        }
    
        $orderItems = $order->orderItems()->get();
        foreach ($orderItems as $orderItem) {
            $orderItem->productVariant()->increment('quantityProduct', $orderItem->quantity);
    
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $orderItem->product_variant_id)
                    ->first();
                if ($cartItem) {
                    $cartItem->quantity += $orderItem->quantity;
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $orderItem->product_id,
                        'product_variant_id' => $orderItem->product_variant_id,
                        'quantity' => $orderItem->quantity,
                        'price' => $orderItem->price
                    ]);
                }
            }
        }
    
        return response()->json(['success' => 'Đơn hàng đã được thêm vào giỏ hàng']);
    }

    public function cancelOrder(Request $request, $id){
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
        }
        $order->update([
            'status' => "cancelled"
        ]);
        Mail::to($order->user->email)->send(new StatusPlacedMail($order));
        return response()->json(['success' => 'Đơn hàng đã hủy']);
    }

    public function handle(Request $request)
    {
        \Log::debug('Webhook GHN:', $request->all());

        $orderCode = $request->input('OrderCode'); // GHN trả về mã đơn hàng của họ (hoặc của mình)
        $status = $request->input('Status');       // Trạng thái GHN gửi về

        // Ví dụ: lấy order theo mã đơn hàng của GHN
        $order = Order::where('tracking_code', $orderCode)->first();

        if ($order) {
            $order->status = $status;
            $order->save();
        }

        return response()->json(['message' => 'OK']);
    }

    public function updateOrderStatusToGHN(Request $request)
    {
        // Validate dữ liệu nhận được từ request
        $request->validate([
            'order_id' => 'required|exists:orders,id', // Kiểm tra tồn tại của order_id trong bảng orders
            'status' => 'required|in:pending,processing,shipping,completed,cancelled', // Kiểm tra trạng thái hợp lệ
        ]);
    
        // Lấy API key từ file .env
        $token = env('GHN_API_KEY');
        
        // Tìm đơn hàng theo order_id
        $order = Order::findOrFail($request->order_id);
    
        // Kiểm tra xem order_code có tồn tại không
        if (!$order->tracking_code) {
            return redirect()->back()->with('error', 'Đơn hàng chưa có mã đơn hàng (OrderCode). Không thể cập nhật trạng thái!');
        }
    
        // Lấy các sản phẩm trong đơn hàng
        $orderItems = OrderItem::where('order_id', $order->id)->get();
    
        // Nếu trạng thái đơn hàng là "completed", giảm số lượng sản phẩm trong kho
        if ($request->status === 'completed') {
            foreach ($orderItems as $orderItem) {
                $orderItem->productVariant()->decrement('quantityProduct', $orderItem->quantity);
            }
        }
    
        // Gửi yêu cầu API cập nhật trạng thái đơn hàng tới GHN
        $response = Http::withHeaders([
            'Token' => $token,  // API key từ tài khoản GHN của bạn
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/update', [
            'order_id' => $request->order_id,
            'status' => $request->status,
            'order_code' => $order->tracking_code, // Thêm thông tin OrderCode
        ]);
    
        // Kiểm tra phản hồi từ GHN
        if ($response->successful()) {
            // Cập nhật trạng thái đơn hàng trong hệ thống của bạn
            $order->status = $request->status;
            $order->save();
    
            // Gửi email thông báo thay đổi trạng thái đơn hàng
            Mail::to($order->user->email)->send(new StatusPlacedMail($order));
    
            return redirect()->back()->with('success', 'Trạng thái đơn hàng đã được cập nhật!');
        } else {
            // In ra thông tin lỗi để kiểm tra khi GHN trả về lỗi
            Log::error('GHN API Error: ' . $response->body());
            
            // Xử lý nếu GHN trả về lỗi
            $errorData = $response->json();
            $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Không xác định';
            
            return redirect()->back()->with('error', 'Cập nhật trạng thái đơn hàng không thành công! Lỗi: ' . $errorMessage);
        }
    }

    public function revenue()
    {
        // 1. Dữ liệu tồn kho (Stock Data)
        $stockData = ProductVariant::join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.name as product_name, SUM(product_variants.quantityProduct) as stock_count')
            ->groupBy('products.id', 'products.name')
            ->get()
            ->toArray();

        $productNames = array_column($stockData, 'product_name');
        $stockCounts = array_column($stockData, 'stock_count');

        // 2. Dữ liệu trạng thái đơn hàng (Order Status Count)
        $statusData = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->toArray();

        $statusLabels = array_column($statusData, 'status');
        $statusCounts = array_column($statusData, 'count');

        // 3. Dữ liệu sản phẩm bán chạy (Best Selling Products)
        $bestSellingData = OrderItem::join('product_variants', 'product_variants.idVariant', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.name, SUM(order_items.quantity) as sold_count')
            ->groupBy('products.id', 'products.name')
            ->orderBy('sold_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        $bestSellingNames = array_column($bestSellingData, 'name');
        $bestSellingCounts = array_column($bestSellingData, 'sold_count');

        // 4. Dữ liệu doanh thu theo ngày
        $orderDates = Order::where('status', 'completed')
            ->pluck('created_at')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        $dailyRevenues = Order::where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(final_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        // 5. Dữ liệu doanh thu theo tháng
        $months = Order::where('status', 'completed')
            ->pluck('created_at')
            ->map(fn($date) => $date->format('Y-m'))
            ->unique()
            ->values()
            ->toArray();

        $monthlyRevenues = Order::where('status', 'completed')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(final_price) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // 6. Tính lợi nhuận (Profit) - Cost từ product_variants
        // Tổng chi phí = SUM(order_items.quantity * product_variants.cost) cho các đơn hoàn thành
        $totalCost = Order::where('status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.idVariant', '=', 'order_items.product_variant_id')
            ->sum(DB::raw('order_items.quantity * product_variants.cost_price'));

        // Tổng doanh thu thực tế (đơn hoàn thành)
        $totalRevenueSuccess = Order::where('status', 'completed')->sum('final_price') ?? 0;

        // Tổng lợi nhuận = Doanh thu thực tế - Tổng chi phí
        $totalProfit = $totalRevenueSuccess - $totalCost;

        // Lợi nhuận theo ngày
        $dailyProfits = Order::where('status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.idVariant', '=', 'order_items.product_variant_id')
            ->selectRaw('DATE(orders.created_at) as date, 
                         SUM(orders.final_price) - SUM(order_items.quantity * product_variants.cost_price) as profit')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('profit', 'date')
            ->toArray();

        // Lợi nhuận theo tháng
        $monthlyProfits = Order::where('status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.idVariant', '=', 'order_items.product_variant_id')
            ->selectRaw('DATE_FORMAT(orders.created_at, "%Y-%m") as month, 
                         SUM(orders.final_price) - SUM(order_items.quantity * product_variants.cost_price) as profit')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('profit', 'month')
            ->toArray();

        // 7. Tổng hợp dữ liệu
        $data = [
            'totalRevenue' => Order::sum('final_price') ?? 0, // Tổng doanh thu tất cả đơn hàng
            'totalRevenueSuccess' => $totalRevenueSuccess, // Doanh thu đơn hoàn thành
            'totalProfit' => $totalProfit ?? 0, // Tổng lợi nhuận
            'totalOrders' => Order::count(), // Tổng số đơn hàng
            'totalSoldProducts' => OrderItem::sum('quantity') ?? 0, // Tổng sản phẩm đã bán
            'totalStock' => ProductVariant::sum('quantityProduct') ?? 0, // Tổng tồn kho
            'productNames' => $productNames ?: [], // Danh sách tên sản phẩm tồn kho
            'stockCounts' => $stockCounts ?: [], // Số lượng tồn kho
            'orderDates' => $orderDates ?: [], // Ngày có đơn hoàn thành
            'dailyRevenues' => $dailyRevenues ?: [], // Doanh thu theo ngày
            'dailyProfits' => $dailyProfits ?: [], // Lợi nhuận theo ngày
            'monthlyRevenues' => $monthlyRevenues ?: [], // Doanh thu theo tháng
            'monthlyProfits' => $monthlyProfits ?: [], // Lợi nhuận theo tháng
            'months' => $months ?: [], // Danh sách tháng
            'orderStatusCount' => $statusData ?: [], // Dữ liệu trạng thái đơn hàng
            'bestSellingProducts' => $bestSellingData ?: [], // Dữ liệu sản phẩm bán chạy
            'statusLabels' => $statusLabels ?: [], // Nhãn trạng thái
            'statusCounts' => $statusCounts ?: [], // Số lượng theo trạng thái
            'bestSellingNames' => $bestSellingNames ?: [], // Tên sản phẩm bán chạy
            'bestSellingCounts' => $bestSellingCounts ?: [], // Số lượng bán chạy
        ];

        // Trả về view với dữ liệu
        return view('admin.dashboard', $data);
    }

    public function updateStatus($orderCode)
    {
        $token = env('GHN_API_KEY');
        $shopId = env('GHN_SHOP_ID');

        $response = Http::withHeaders([
            'Token' => $token,
            'ShopId' => $shopId
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail', [
            'order_code' => $orderCode
        ]);

        if ($response->successful()) {
            $ghnStatus = $response['data']['status'] ?? 'unknown';

            // Ánh xạ trạng thái GHN -> Trạng thái hệ thống của đại ca
            $status = $this->mapGhnStatus($ghnStatus);

            // Cập nhật vào database
            $order = Order::where('tracking_code', $orderCode)->first();
            if ($order) {
                $order->status = $status;
                $order->save();
            }

            return response()->json([
                'message' => 'Đã cập nhật trạng thái đơn hàng thành công!',
                'status' => $status
            ]);
        }

        return response()->json([
            'message' => 'Không thể lấy thông tin đơn hàng GHN!',
            'error' => $response->body()
        ], 400);
    }

    private function mapGhnStatus($ghnStatus)
    {
        return match ($ghnStatus) {
            'ready_to_pick' => 'Chờ lấy hàng',
            'picking' => 'Đang lấy hàng',
            'picked' => 'Đã lấy hàng',
            'delivering' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancel' => 'Đã hủy',
            'return' => 'Đang trả hàng',
            'returned' => 'Đã trả hàng',
            'storing' => 'Lưu kho',
            default => 'Không xác định'
        };
    }

}
   