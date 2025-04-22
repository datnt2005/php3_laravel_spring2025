<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'discounts';

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type', // 'percent' or 'fixed'
        'discount_value',
        'min_order_value',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'status', // 'active', 'inactive', 'expired'
        'is_active',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    // public function order()
    // {
    //     return $this->hasMany(Order::class);
    // }
}
