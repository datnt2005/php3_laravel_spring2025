<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['name', 'slug', 'description', 'brand_id'];
    public function productVariants (){
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }   

    public function productPic(){
        return $this->hasMany(ProductPic::class, 'product_id', 'id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class , 'brand_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }
    
}
