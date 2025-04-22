<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'address_id',
        'note',
        'status',
        'payment_method',
        'discount_id', 
        'total_price',
        'discount_price',
        'shipping_fee',
        'final_price',
        'tracking_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class) ;
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function discount()
    {
        return $this->belongsTo(Coupon::class);
    }
}
