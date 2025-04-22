<?php

namespace App\Http\Controllers;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
      $favorites = Favorite::where('user_id', Auth::user()->id)->get();
        
        return view('user.favorite', compact('favorites'));
    }
    
    public function toggleFavorite($product_id)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Đã xóa khỏi danh sách yêu thích');
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'product_id' => $product_id,
            ]);
            return back()->with('success', 'Đã thêm vào danh sách yêu thích');
        }
    }
}
