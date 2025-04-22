<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantAttribute;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductPic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return view('user.cart', ['cartItems' => [], 'cart' => null, 'totalQuantity' => 0, 'totalPrice' => 0]);
        }

        $cartItems = CartItem::where('cart_id', $cart->id)->with('productVariant')->get();
        
        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price));

        session(['cart' => $totalQuantity]);

        return view('user.cart', compact('cartItems', 'cart', 'totalQuantity', 'totalPrice'));
    }

    public function addToCart(Request $request)
    {
        $user = Auth::user();
    
        // Check if the user is logged in
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng!'
            ]);
        }
    
        // Validate input
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,idVariant',
            'quantity-cart' => 'required|integer|min:1',
        ]);
    
        // Validate product existence
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ]);
        }
    
        // Validate variant existence
        $variant = ProductVariant::with('attributes')->find($request->variant_id);
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Biến thể sản phẩm không tồn tại! Vui lòng chọn kích thước và màu sắc chính xác.'
            ]);
        }
    
        // Check if the variant belongs to the product
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Biến thể sản phẩm không hợp lệ!'
            ]);
        }
    
        // Validate quantity
        $quantity = (int) $request->input('quantity-cart');
        if ($quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng không hợp lệ!'
            ]);
        }
    
        // Check stock availability
        if ($quantity > $variant->quantityProduct) {
            return response()->json([
                'success' => false,
                'message' => "Số lượng vượt quá tồn kho! Tồn kho hiện tại: {$variant->quantityProduct}"
            ]);
        }
    
        // Get or create the user's cart
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'pending']
        );
    
        // Check if the item already exists in the cart
        $existingCartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->idVariant)
            ->first();
    
        $totalQuantity = $quantity;
    
        if ($existingCartItem) {
            // If the item exists, add the new quantity to the existing quantity
            $totalQuantity = $existingCartItem->quantity + $quantity;
    
            // Check stock availability for the updated quantity
            if ($totalQuantity > $variant->quantityProduct) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng vượt quá tồn kho! Tồn kho hiện tại: {$variant->quantityProduct}"
                ]);
            }
    
            // Update existing cart item
            $existingCartItem->update([
                'quantity' => $totalQuantity,
                'price' => $variant->sale_price ?? $variant->price,
            ]);
            $cartItem = $existingCartItem;
        } else {
            // Add new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'product_variant_id' => $variant->idVariant,
                'quantity' => $totalQuantity,
                'price' => $variant->sale_price ?? $variant->price,
                'sku' => $variant->sku,
            ]);
        }
    
        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng!',
            'cartItem' => $cartItem,
            'cartTotalQuantity' => $cart->cartItems->sum('quantity'),
            'cartTotalPrice' => $cart->cartItems->sum(fn ($item) => $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price)),
            'redirect' => route('product.show', $product->slug)
        ]);
    }
    public function delete($id)
    {
        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return redirect()->route('cart.index')->with('error', 'Sản phẩm không tồn tại trong giỏ hàng!');
        }

        $cartItem->delete();
        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xóa khỏi giỏ hàng!');
    }

    public function update(Request $request, $id)
    {
        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return response()->json(['status' => 'error', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'], 404);
        }

        $quantity = max(1, (int) $request->input('quantity', 1));
        if ($quantity > $cartItem->productVariant->quantityProduct) {
            return response()->json(['status' => 'error', 'message' => 'Số lượng vượt quá tồn kho.'], 400);
        }

        $cartItem->quantity = $quantity;
        $cartItem->price = $cartItem->productVariant->sale_price ?? $cartItem->productVariant->price; // Update price if changed
        $cartItem->save();

        $cart = Cart::where('user_id', Auth::id())->first();
        $cartItems = CartItem::where('cart_id', $cart->id)->with('productVariant')->get();

        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật số lượng thành công.',
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
            'updatedPrice' => $cartItem->quantity * ($cartItem->productVariant->sale_price ?? $cartItem->productVariant->price)
        ]);
    }

    public function clearCart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }
        return redirect()->route('cart.index')->with('success', 'Giỏ hàng đã được xóa!');
    }
}