<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['product_variant_id', 'attribute_id', 'value_id'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class , 'product_variant_id', 'idVariant');
    }
    public function attribute()
    {
        return $this->belongsTo(Attribute::class , 'attribute_id', 'id');
    }

    public function value()
    {
        return $this->belongsTo(AttributeValue::class , 'value_id', 'id');
    }
}
