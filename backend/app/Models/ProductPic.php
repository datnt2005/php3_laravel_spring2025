<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPic extends Model
{
    use HasFactory;

    protected $table = 'product_pic';

    protected $fillable = [
        'product_id',
        'imagePath',
    ];
    public function product(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
