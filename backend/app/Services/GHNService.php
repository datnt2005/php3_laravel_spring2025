<?php

namespace App\Services;
use App\Models\Order;
use App\Models\Address;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    protected $token;
    protected $shopId;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.ghn.token');
        $this->shopId = config('services.ghn.shop_id');
        $this->baseUrl = config('services.ghn.base_url');
    }

    public function calculateFee($toDistrictId, $toWardCode, $weight)
    {
        $response = Http::withHeaders([
            'Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/shipping-order/fee', [
            "from_district_id" => 1450, // ví dụ Quận 1 HCM
            "service_type_id" => 2,
            "to_district_id" => $toDistrictId,
            "to_ward_code" => $toWardCode,
            "height" => 15,
            "length" => 15,
            "weight" => $weight,
            "width" => 15,
            "insurance_value" => 1000000,
            "shop_id" => $this->shopId,
        ]);

        return $response->json();
    }

    public function createGhnOrder(Order $order)
{
    try {
        // Lấy địa chỉ giao hàng từ đơn hàng
        $address = Address::find($order->address_id);
        if (!$address) {
            Log::error('Địa chỉ không tồn tại cho đơn hàng ID: ' . $order->id);
            return false;
        }

        if (empty($address->ward_code) || empty($address->district_id)) {
            Log::error('Địa chỉ thiếu mã phường hoặc quận cho đơn hàng ID: ' . $order->id);
            return false;
        }

        if (!preg_match('/^[0-9]{10,11}$/', $address->phone)) {
            Log::error('Số điện thoại không hợp lệ: ' . $address->phone);
            return false;
        }

        // Tạo danh sách sản phẩm
        $items = [];
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $variant = $item->productVariant;

            $items[] = [
                'name' => $product->name ?? 'Sản phẩm không rõ',
                'code' => $variant->sku ?? 'SP-' . $item->id,
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
                'length' => $variant->length ?? 10,
                'width' => $variant->width ?? 10,
                'height' => $variant->height ?? 10,
                'weight' => $variant->weight ?? 200,
                'category' => [
                    'level1' => $product->category->name ?? 'Chưa phân loại',
                ],
            ];
        }

        // Tổng khối lượng
        $totalWeight = array_sum(array_column($items, 'weight'));

        // Dữ liệu gửi đi
        $requestData = [
            "payment_type_id" => 2,
            "note" => $order->note ?? 'Không có ghi chú',
            "required_note" => "KHONGCHOXEMHANG",
            "to_name" => $address->name,
            "to_phone" => $address->phone,
            "to_address" => $address->detail,
            "to_ward_code" => $address->ward_code,
            "to_district_id" => (int) $address->district_id,
            "cod_amount" => (int) $order->final_price,
            "content" => "Đơn hàng #" . $order->id,
            "weight" => $totalWeight > 0 ? $totalWeight : 500,
            "length" => 20,
            "width" => 15,
            "height" => 10,
            "service_type_id" => 2,
            "items" => $items,
            "shop_id" => $this->shopId,
        ];

        Log::debug('Dữ liệu gửi GHN cho đơn hàng ID ' . $order->id, $requestData);

        $response = Http::withHeaders([
            'Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/shipping-order/create', $requestData);

        if ($response->successful() && isset($response['data']['order_code'])) {
            $order->tracking_code = $response['data']['order_code'];
            $order->save();
            Log::info('Tạo đơn GHN thành công: ' . $order->tracking_code);
            return true;
        } else {
            Log::error('Lỗi tạo đơn GHN: ' . $response->body());
            Log::debug('GHN Token: ' . $this->token);
            return false;
        }

    } catch (\Exception $e) {
        Log::error('Lỗi Exception khi tạo đơn GHN: ' . $e->getMessage());
        return false;
    }
}

}
