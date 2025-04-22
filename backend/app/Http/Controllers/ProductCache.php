<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;

class ProductCache extends Controller
{
    public function cacheProducts()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $key = 'product:' . $product->id;

            Redis::hmset($key, [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'slug' => $product->slug,
            ]);
        }

        return response()->json(['message' => 'Products cached successfully']);
    }
}

