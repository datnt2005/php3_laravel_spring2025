<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Favorite;
use App\Models\ProductVariant;
use App\Models\ProductPic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public function index()
    {
        $products = Product::all()->take(4);
        $favoriteIds = auth()->check()
        ? auth()->user()->favorites()->pluck('product_id')->toArray()
        : [];
        $banners = Banner::where('status', 'active')->get();
        return view('user.home', compact( 'products', 'favoriteIds', 'banners'));
    }
}
