<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Mail\StatusPlacedMail;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    
        public function getProvinces()
    {
        $token = env('GHN_API_KEY');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => $token,
        ])->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province');

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        } else {
            return response()->json(['error' => 'Không thể lấy danh sách tỉnh', 'detail' => $response->json()], $response->status());
        }
    }

    public function getDistricts($province_id)
    {
        $token = env('GHN_API_KEY'); // Token từ .env

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => $token
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district', [
            'province_id' => (int)$province_id
        ]);

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        } else {
            return response()->json([
                'error' => 'Không thể lấy danh sách quận/huyện',
                'detail' => $response->json()
            ], $response->status());
        }
    }
    public function getWards($district_id)
    {
        $token = env('GHN_API_KEY'); // Token GHN

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => $token
        ])->get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward', [
            'district_id' => (int)$district_id // Truyền district_id vào query string
        ]);

        if ($response->successful()) {
            return response()->json($response->json()['data']);
        } else {
            return response()->json([
                'error' => 'Không thể lấy danh sách phường/xã',
                'detail' => $response->json()
            ], $response->status());
        }
    }

    public function calculateShippingFee(Request $request)
    {
        $token = env('GHN_API_KEY');
        $shopId = (int) env('GHN_SHOP_ID');
        $toDistrictId = $request->input('to_district_id');
        $toWardCode = $request->input('to_ward_code');
        $serviceId = $request->input('service_id', 53321); // Mặc định là Tiêu chuẩn
        $weight = $request->input('weight', 1000);
        $height = $request->input('height', 10);
        $length = $request->input('length', 20);
        $width = $request->input('width', 15);

        // Kiểm tra dữ liệu đầu vào
        if (!$toDistrictId || !$toWardCode || !$serviceId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thiếu thông tin bắt buộc: to_district_id, to_ward_code hoặc service_id!'
            ], 400);
        }

        try {
            $response = Http::withHeaders(['Token' => $token])
                ->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
                    'from_district_id' => (int) $shopId, // Ép kiểu int ở đây
                'service_id' => (int) $serviceId,
                'to_district_id' => (int) $toDistrictId,
                'to_ward_code' => (string) $toWardCode,
                'weight' => (int) $weight,
                'height' => (int) $height,
                'length' => (int) $length,
                'width' => (int) $width
                ]);
                        
                $data = $response->json();

            if (isset($data['data']['total'])) {
                return response()->json([
                    'status' => 'success',
                    'shipping_fee' => $data['data']['total']
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $data['message'] ?? 'Không thể tính phí ship từ GHN'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi gọi API GHN: ' . $e->getMessage()
            ], 500);
        }
    }

    
}