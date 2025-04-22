<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'product_variants';
    protected $primaryKey = 'idVariant';
    protected $fillable = [
        'idVariant',
        'sku',
        'price',
        'sale_price',
        'cost_price',
        'quantityProduct',
        'product_id',
        'image',
    ];
    public function attributes(){
        return $this->hasMany(VariantAttribute::class, 'product_variant_id', 'idVariant');
    }
    
    public function product(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
