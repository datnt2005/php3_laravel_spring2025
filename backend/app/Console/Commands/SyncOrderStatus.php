<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use App\Mail\StatusPlacedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

class SyncOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ghn:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ trạng thái đơn hàng từ GHN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::whereNotNull('tracking_code')->get();

        if ($orders->isEmpty()) {
            $this->info("📭 Không có đơn hàng nào cần cập nhật.");
            return;
        }

        foreach ($orders as $order) {
            $response = Http::withHeaders([
                'Token' => env('GHN_API_KEY'),
                'ShopId' => env('GHN_SHOP_ID'),
                'Content-Type' => 'application/json'
            ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail', [
                'order_code' => $order->tracking_code
            ]);

            if ($response->successful()) {
                $statusCode = $response->json('data.status') ?? null;

                if ($statusCode) {
                    $mappedStatus = $this->mapGhnStatus($statusCode);
                    $order->status = $mappedStatus;
                    $order->save();
                    Mail::to($order->user->email)->send(new StatusPlacedMail($order));
                    $this->info("✅ Đã cập nhật đơn hàng {$order->tracking_code} => Trạng thái: {$mappedStatus}");
                } else {
                    $this->warn("⚠️ Không có trạng thái trả về cho đơn {$order->tracking_code}");
                }
            } else {
                $this->error("❌ Lỗi khi lấy trạng thái đơn hàng {$order->tracking_code}: " . $response->body());
            }
        }
    }

    /**
     * Ánh xạ trạng thái từ GHN về trạng thái của hệ thống
     */
    protected function mapGhnStatus($ghnStatus)
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

    protected function schedule(Schedule $schedule)
{
    $schedule->command('ghn:sync-status')->daily(); 
}

}
