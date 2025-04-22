<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Mail\StatusPlacedMail;
use Illuminate\Support\Facades\Mail;

class UpdateGHNStatus extends Command
{
    protected $signature = 'ghn:update-status {orderCode}';
    protected $description = 'Cập nhật trạng thái đơn hàng từ GHN';

    public function handle()
    {
        $orderCode = $this->argument('orderCode');

        // Gửi request đến GHN
        $response = Http::withHeaders([
            'Token' => env('GHN_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail', [
            'order_code' => $orderCode
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $ghnStatus = $data['data']['status'] ?? null;

            if ($ghnStatus) {
                $order = Order::where('tracking_code', $orderCode)->first();

                if ($order) {
                    $convertedStatus = $this->mapGhnStatus($ghnStatus);
                    $order->status = $convertedStatus;
                    $order->save();
                    Mail::to($order->user->email)->send(new StatusPlacedMail($order));
                    $this->info("✅ Đã cập nhật trạng thái: {$convertedStatus} (GHN: {$ghnStatus}) cho đơn hàng {$orderCode}");
                } else {
                    $this->error("❌ Không tìm thấy đơn hàng trong database với mã GHN: {$orderCode}");
                }
            } else {
                $this->error('⚠️ Không tìm thấy trạng thái đơn hàng trong phản hồi GHN.');
            }
        } else {
            $this->error("🔥 Lỗi GHN API: " . $response->body());
        }
    }

    private function mapGhnStatus($ghnStatus)
    {
        return match ($ghnStatus) {
            'ready_to_pick'     => 'ready_to_pick',
            'picking'           => 'picking',
            'picked'            => 'picked',
            'transporting'      => 'transporting',
            'delivering'        => 'delivering',
            'delivered'         => 'delivered',
            'delivery_fail'     => 'delivery_fail',
            'cancel'            => 'cancel',
            default             => $ghnStatus,
        };
    }
}
