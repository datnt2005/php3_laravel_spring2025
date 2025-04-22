<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class AIChatController extends Controller
{
    public function chat(Request $request)
    {
        $prompt = $request->input('message');

        if (empty($prompt)) {
            return response()->json([
                'response' => 'Vui lòng nhập câu hỏi hoặc nội dung để trò chuyện.'
            ], 400);
        }

        try {
            // Local parsing for product queries
            $criteria = $this->localParsePrompt($prompt);

            // If not a product query, use Gemini API for general conversation
            if ($criteria['intent'] === 'general_conversation') {
                $apiResponse = $this->callGeminiAPI($prompt);
                $criteria = $this->parseAIResponse($apiResponse);
            }

            // Process the request based on intent
            $finalResponse = $this->processUserRequest($prompt, $criteria);

            return response()->json([
                'response' => $finalResponse
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý yêu cầu: ' . $e->getMessage(), [
                'prompt' => $prompt,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'response' => 'Xin lỗi, có lỗi xảy ra. Bạn có thể thử lại với mô tả khác không?'
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng!',
                'redirect' => route('login')
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,idVariant',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại!'
                ], 404);
            }

            $variant = ProductVariant::with('attributes')->find($request->variant_id);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể sản phẩm không tồn tại! Vui lòng chọn kích thước và màu sắc chính xác.'
                ], 404);
            }

            if ($variant->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể sản phẩm không hợp lệ!'
                ], 400);
            }

            $quantity = (int) $request->input('quantity');
            if ($quantity > $variant->quantityProduct) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng vượt quá tồn kho! Tồn kho hiện tại: {$variant->quantityProduct}"
                ], 400);
            }

            // Kiểm tra thuộc tính màu sắc và kích thước
            $color = $variant->attributes->where('attribute.name', 'color')->first()->value ?? 'Chưa xác định';
            $size = $variant->attributes->where('attribute.name', 'size')->first()->value ?? 'Chưa xác định';
            if ($color === 'undefined' || $size === 'undefined') {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể sản phẩm chưa được cấu hình đúng. Vui lòng liên hệ quản trị viên.'
                ], 400);
            }

            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => 'pending']
            );

            $existingCartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->idVariant)
                ->first();

            $totalQuantity = $quantity;

            if ($existingCartItem) {
                $totalQuantity = $existingCartItem->quantity + $quantity;
                if ($totalQuantity > $variant->quantityProduct) {
                    return response()->json([
                        'success' => false,
                        'message' => "Số lượng vượt quá tồn kho! Tồn kho hiện tại: {$variant->quantityProduct}"
                    ], 400);
                }

                $existingCartItem->update([
                    'quantity' => $totalQuantity,
                    'price' => $variant->sale_price ?? $variant->price,
                ]);
                $cartItem = $existingCartItem;
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'product_variant_id' => $variant->idVariant,
                    'quantity' => $totalQuantity,
                    'price' => $variant->sale_price ?? $variant->price,
                    'sku' => $variant->sku,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng!',
                'cartItem' => $cartItem,
                'cartTotalQuantity' => $cart->cartItems->sum('quantity'),
                'cartTotalPrice' => $cart->cartItems->sum(fn($item) => $item->quantity * ($item->productVariant->sale_price ?? $item->productVariant->price)),
                'redirect' => route('cart.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi thêm sản phẩm vào giỏ hàng: ' . $e->getMessage(), [
                'product_id' => $request->product_id,
                'product_variant_id' => $request->variant_id,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.'
            ], 500);
        }
    }

    private function localParsePrompt($prompt)
    {
        $lowercasePrompt = strtolower($prompt);

        // Keywords indicating a shop info query
        $shopInfoKeywords = ['của ai', 'ai là người lập', 'lập khi nào', 'chủ shop', 'người sáng lập', 'thành lập'];
        $isShopInfoQuery = false;
        foreach ($shopInfoKeywords as $keyword) {
            if (str_contains($lowercasePrompt, $keyword)) {
                $isShopInfoQuery = true;
                break;
            }
        }

        if ($isShopInfoQuery) {
            return [
                'intent' => 'shop_info',
                'category' => null,
                'color' => null,
                'size' => null,
                'price_range' => null,
                'keywords' => [],
                'other' => null,
                'general_response' => null
            ];
        }

        // Keywords indicating a product details query
        $detailKeywords = ['chi tiết', 'xem chi tiết', 'thông tin chi tiết', 'chi tiết sản phẩm'];
        $isDetailQuery = false;
        foreach ($detailKeywords as $keyword) {
            if (str_contains($lowercasePrompt, $keyword)) {
                $isDetailQuery = true;
                break;
            }
        }

        if ($isDetailQuery) {
            // Extract product name or identifier from the prompt
            $productName = trim(str_replace($detailKeywords, '', $lowercasePrompt));
            return [
                'intent' => 'view_product_details',
                'category' => null,
                'color' => null,
                'size' => null,
                'price_range' => null,
                'keywords' => [$productName],
                'other' => null,
                'general_response' => null
            ];
        }

        // Keywords indicating a product query
        $productKeywords = ['mua', 'tìm', 'sản phẩm', 'áo', 'quần', 'giày', 'màu', 'kích thước', 'size', 'giá'];
        $isProductQuery = false;
        foreach ($productKeywords as $keyword) {
            if (str_contains($lowercasePrompt, $keyword)) {
                $isProductQuery = true;
                break;
            }
        }

        // Check for trending products
        if (str_contains($lowercasePrompt, 'trending') || str_contains($lowercasePrompt, 'nổi bật') || str_contains($lowercasePrompt, 'thịnh hành')) {
            return [
                'intent' => 'list_trending',
                'category' => null,
                'color' => null,
                'size' => null,
                'price_range' => null,
                'keywords' => [],
                'other' => null,
                'general_response' => null
            ];
        }

        // Parse product query
        if ($isProductQuery) {
            $category = null;
            $color = null;
            $size = null;
            $keywords = [];

            // Detect category
            if (str_contains($lowercasePrompt, 'áo thun')) {
                $category = 'áo thun nam';
                $keywords[] = 'áo thun';
            } elseif (str_contains($lowercasePrompt, 'quần')) {
                $category = 'quần nam';
                $keywords[] = 'quần';
            } elseif (str_contains($lowercasePrompt, 'giày')) {
                $category = 'giày nam';
                $keywords[] = 'giày';
            }

            // Detect color
            if (str_contains($lowercasePrompt, 'xanh')) {
                $color = 'xanh';
            } elseif (str_contains($lowercasePrompt, 'đen')) {
                $color = 'đen';
            } elseif (str_contains($lowercasePrompt, 'trắng')) {
                $color = 'trắng';
            }

            // Detect size
            if (str_contains($lowercasePrompt, 'size s') || str_contains($lowercasePrompt, 'nhỏ')) {
                $size = 'S';
            } elseif (str_contains($lowercasePrompt, 'size m') || str_contains($lowercasePrompt, 'vừa')) {
                $size = 'M';
            } elseif (str_contains($lowercasePrompt, 'size l') || str_contains($lowercasePrompt, 'lớn')) {
                $size = 'L';
            }

            // Detect gender
            if (str_contains($lowercasePrompt, 'nam')) {
                $keywords[] = 'nam';
            } elseif (str_contains($lowercasePrompt, 'nữ')) {
                $keywords[] = 'nữ';
            }

            return [
                'intent' => 'search_product',
                'category' => $category,
                'color' => $color,
                'size' => $size,
                'price_range' => null,
                'keywords' => array_filter($keywords),
                'other' => null,
                'general_response' => null
            ];
        }

        // Default to general conversation
        return [
            'intent' => 'general_conversation',
            'category' => null,
            'color' => null,
            'size' => null,
            'price_range' => null,
            'keywords' => [],
            'other' => null,
            'general_response' => null
        ];
    }

    private function callGeminiAPI($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');
        $url = env('GEMINI_URL');

        if (empty($apiKey)) {
            Log::error('GEMINI_API_KEY không được thiết lập trong .env');
            throw new \Exception('Lỗi cấu hình hệ thống.');
        }

        $response = Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$url}{$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $this->prepareAIPrompt($prompt)]
                    ]
                ]
            ]
        ]);

        if (!$response->successful()) {
            Log::error('Yêu cầu API thất bại', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Không thể kết nối tới dịch vụ AI.');
        }

        $data = $response->json();
        Log::info('Gemini API Response', $data);

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private function prepareAIPrompt($userPrompt)
    {
        return <<<EOT
Bạn là một trợ lý AI thông minh. Hãy trả lời câu hỏi hoặc yêu cầu của người dùng bằng tiếng Việt một cách tự nhiên và hữu ích.

Yêu cầu người dùng: "$userPrompt"

Trả về câu trả lời trực tiếp, không cần định dạng JSON, ví dụ:
- Nếu người dùng hỏi: "Hôm nay thời tiết thế nào?"
  Trả về: "Hôm nay thời tiết ở khu vực của bạn thế nào? Bạn có thể cho tôi biết địa điểm cụ thể để kiểm tra không?"
EOT;
    }

    private function parseAIResponse($aiResponse)
    {
        if (!$aiResponse) {
            return [
                'intent' => 'general_conversation',
                'category' => null,
                'color' => null,
                'size' => null,
                'price_range' => null,
                'keywords' => [],
                'other' => null,
                'general_response' => 'Xin lỗi, tôi không hiểu rõ yêu cầu. Bạn có thể nói rõ hơn không?'
            ];
        }

        return [
            'intent' => 'general_conversation',
            'category' => null,
            'color' => null,
            'size' => null,
            'price_range' => null,
            'keywords' => [],
            'other' => null,
            'general_response' => $aiResponse
        ];
    }

    private function processUserRequest($prompt, $criteria)
    {
        $intent = $criteria['intent'] ?? 'general_conversation';

        if ($intent === 'list_trending') {
            return $this->getTrendingProducts();
        }

        if ($intent === 'search_product') {
            return $this->searchProducts($criteria, $prompt);
        }

        if ($intent === 'shop_info') {
            return $this->getShopInfo();
        }

        if ($intent === 'view_product_details') {
            return $this->getProductDetails($criteria, $prompt);
        }

        return $criteria['general_response'] ?? "Xin lỗi, tôi không hiểu rõ yêu cầu. Bạn có thể nói rõ hơn không?";
    }

    private function getShopInfo()
    {
        return <<<EOT
Cửa hàng này được thành lập và thuộc sở hữu của ông Nguyễn Tiến Đạt, sinh viên FPOLY Tây Nguyên. 
Cửa hàng được thành lập vào mùa xuân năm 2025. 
Chúng tôi tự hào mang đến những sản phẩm chất lượng và dịch vụ tốt nhất cho khách hàng!💕
EOT;
    }

    private function getProductDetails($criteria, $originalPrompt)
    {
        try {
            $productName = $criteria['keywords'][0] ?? '';
            if (empty($productName)) {
                return "Xin lỗi, vui lòng cung cấp tên sản phẩm để xem chi tiết, ví dụ: 'chi tiết áo thun nam đen'.";
            }

            $product = Product::query()
                ->with(['productVariants.attributes.attribute', 'productPic'])
                ->where('name', 'like', '%' . $productName . '%')
                ->orWhere('slug', 'like', '%' . $productName . '%')
                ->first();

            if (!$product) {
                return "Xin lỗi, không tìm thấy sản phẩm với tên: '$productName'. Bạn có thể thử lại với tên khác hoặc kiểm tra danh sách sản phẩm bằng cách hỏi 'tìm áo thun nam'.";
            }

            $response = "Chi tiết sản phẩm: {$product->name}\n";
            $response .= "Mô tả: " . ($product->description ?? 'Không có mô tả') . "\n";

            // Product images
            $images = $product->productPic->pluck('imagePath')->map(function ($path) {
                return asset('storage/' . $path);
            })->implode(', ');
            $response .= "Hình ảnh: " . ($images ?: asset('images/no-image.png')) . "\n";
            $response .= "ID sản phẩm: {$product->id}\n";
            $response .= "Các biến thể:\n";
            $hasValidVariants = false;

            foreach ($product->productVariants as $variant) {
                $price = $variant->sale_price ?? $variant->price;
            
                $color = 'Chưa xác định';
                $size = 'Chưa xác định';
            
                foreach ($variant->attributes as $attr) {
                    if (!$attr->attribute) continue;
            
                    if ($attr->attribute->name === 'color' && !empty($attr->value) && $attr->value !== 'undefined') {
                        $color = $attr->value;
                    }
            
                    if ($attr->attribute->name === 'size' && !empty($attr->value) && $attr->value !== 'undefined') {
                        $size = $attr->value;
                    }
                }
            
                // Bỏ qua nếu thiếu color hoặc size
                if ($color === 'Chưa xác định' || $size === 'Chưa xác định') {
                    continue;
                }
            
                $stock = $variant->quantityProduct;
                $hasValidVariants = true;
            
                $response .= "- Màu: {$color}, Kích thước: {$size}, ID biến thể: {$variant->idVariant}, ID sản phẩm: {$product->id}\n";
                $response .= "  Giá: " . number_format($price) . " VNĐ\n";
                $response .= "  Tồn kho: {$stock} sản phẩm\n";
            }

            if (!$hasValidVariants) {
                $response .= "Cảnh báo: Tất cả biến thể của sản phẩm này chưa được cấu hình đúng. Vui lòng liên hệ quản trị viên để cập nhật.\n";
            }

            $response .= "Link sản phẩm: " . url("/product/{$product->slug}") . "\n";
            $response .= "Bạn muốn tìm thêm sản phẩm tương tự hay cần hỗ trợ gì nữa không?";
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Lỗi lấy chi tiết sản phẩm: ' . $e->getMessage(), [
                'criteria' => $criteria,
                'exception' => $e->getTraceAsString()
            ]);
            return "Xin lỗi, không thể lấy chi tiết sản phẩm lúc này. Vui lòng thử lại sau hoặc cung cấp tên sản phẩm chính xác hơn.";
        }
    }
    private function searchProducts($criteria, $originalPrompt)
    {
        try {
            $query = Product::query()->with(['productVariants.attributes']);

            // Category filter
            if (!empty($criteria['category'])) {
                if (Schema::hasTable('categories')) {
                    $query->whereHas('categories', function ($q) use ($criteria) {
                        $q->where('name', 'like', '%' . $criteria['category'] . '%');
                    });
                } else {
                    $query->where('name', 'like', '%' . $criteria['category'] . '%')
                        ->orWhere('description', 'like', '%' . $criteria['category'] . '%');
                }
            }

            // Keyword filter
            if (!empty($criteria['keywords'])) {
                $query->where(function ($q) use ($criteria) {
                    foreach ($criteria['keywords'] as $keyword) {
                        $q->orWhere('name', 'like', '%' . $keyword . '%')
                            ->orWhere('description', 'like', '%' . $keyword . '%');
                    }
                });
            }

            // Color filter
            if (!empty($criteria['color'])) {
                $query->whereHas('productVariants.attributes', function ($q) use ($criteria) {
                    $q->where('value', 'like', '%' . $criteria['color'] . '%')
                        ->whereHas('attribute', function ($q) {
                            $q->where('name', 'color');
                        });
                });
            }

            // Size filter
            if (!empty($criteria['size'])) {
                $query->whereHas('productVariants.attributes', function ($q) use ($criteria) {
                    $q->where('value', 'like', '%' . $criteria['size'] . '%')
                        ->whereHas('attribute', function ($q) {
                            $q->where('name', 'size');
                        });
                });
            }

            // Only include products with available variants
            $query->whereHas('productVariants', function ($q) {
                $q->where('quantityProduct', '>', 0);
            });

            $products = $query->take(5)->get();

            if ($products->isEmpty()) {
                return "Xin lỗi, không tìm thấy sản phẩm phù hợp với yêu cầu: '$originalPrompt'. Bạn có muốn thử mô tả khác, ví dụ 'áo thun nam màu đen' hoặc 'quần jeans'?";
            }

            $response = "Dựa trên yêu cầu của bạn, đây là các sản phẩm phù hợp:\n";
            foreach ($products as $product) {
                $variant = $product->productVariants->first();
                if ($variant) {
                    $price = $variant->sale_price ?? $variant->price;
                    $color = $variant->attributes->where('attribute.name', 'color')->first()->value ?? 'N/A';
                    $size = $variant->attributes->where('attribute.name', 'size')->first()->value ?? 'N/A';
                    $image = $product->productPic->first() ? asset('storage/' . $product->productPic->first()->imagePath) : asset('images/no-image.png');
                    $response .= "- {$product->name} ({$color}, {$size}, ID biến thể: {$variant->idVariant}, ID sản phẩm: {$product->id}): " . number_format($price) . " VNĐ\n";
                    $response .= "  Link: " . url("/product/{$product->slug}") . "\n";
                    $response .= "  Image: {$image}\n";
                }
            }
            $response .= "Nếu bạn muốn xem chi tiết sản phẩm, hãy nói: 'chi tiết [tên sản phẩm]'.";

            return $response;
        } catch (\Exception $e) {
            Log::error('Lỗi tìm kiếm sản phẩm: ' . $e->getMessage(), [
                'criteria' => $criteria,
                'exception' => $e->getTraceAsString()
            ]);
            return "Xin lỗi, không tìm thấy sản phẩm phù hợp lúc này. Bạn có thể thử mô tả khác, ví dụ 'áo thun nam màu đen' hoặc 'quần jeans'?";
        }
    }

    private function getTrendingProducts()
    {
        try {
            if (!Schema::hasTable('order_items')) {
                $trendingProducts = Product::query()
                    ->with(['productVariants.attributes.attribute', 'productPic'])
                    ->whereHas('productVariants', function ($q) {
                        $q->where('quantityProduct', '>', 0);
                    })
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            } else {
                $productIds = Product::query()
                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->join('order_items', 'product_variants.idVariant', '=', 'order_items.product_variant_id')
                    ->select('products.id')
                    ->groupBy('products.id')
                    ->orderByRaw('SUM(order_items.quantity) DESC')
                    ->take(5)
                    ->pluck('id');
    
                $trendingProducts = Product::with(['productVariants.attributes.attribute', 'productPic'])
                    ->whereIn('id', $productIds)
                    ->get();
            }
    
            if ($trendingProducts->isEmpty()) {
                return "Hiện tại chưa có sản phẩm nào nổi bật. Bạn muốn tìm sản phẩm theo tiêu chí khác, ví dụ 'áo thun nam màu xanh'?";
            }
    
            $response = "🔥 Dưới đây là Top 5 sản phẩm bán chạy:\n\n";
    
            foreach ($trendingProducts as $product) {
                $variant = $product->productVariants->first();
                if ($variant) {
                    $price = $variant->sale_price ?? $variant->price ?? 0;
                    $image = optional($product->productPic->first())->imagePath
                        ? asset('storage/' . $product->productPic->first()->imagePath)
                        : asset('images/no-image.png');
    
                    $color = 'Không xác định';
                    $size = 'Không xác định';
                    foreach ($variant->attributes as $attribute) {
                        $attributeValue = is_string($attribute->value) ? json_decode($attribute->value, true) : $attribute->value;
                        if (strtolower($attribute->attribute->name) === 'color' || strtolower($attribute->attribute->name) === 'màu') {
                            $color = is_array($attributeValue) ? ($attributeValue['value'] ?? 'Không xác định') : ($attributeValue ?? 'Không xác định');
                        }
                        if (strtolower($attribute->attribute->name) === 'size' || strtolower($attribute->attribute->name) === 'kích thước') {
                            $size = is_array($attributeValue) ? ($attributeValue['value'] ?? 'Không xác định') : ($attributeValue ?? 'Không xác định');
                        }
                    }
    
                    $response .= "🛍️ {$product->name}\n";
                    $response .= "- Màu: {$color}, Kích thước: {$size}\n";
                    $response .= "- Giá: " . number_format($price) . " VNĐ\n";
                    $response .= "- 🔗 Link: " . url("/product/{$product->slug}") . "\n";
                    $response .= "- 🖼️ Hình ảnh: {$image}\n";
                    $response .= "- 🆔 ID Biến thể: {$variant->idVariant}, ID Sản phẩm: {$product->id}\n";
                    $response .= str_repeat("-", 40) . "\n";
                }
            }
    
            $response .= "\n👉 Nếu bạn muốn xem chi tiết sản phẩm nào, hãy nói: `chi tiết [tên sản phẩm]`.";
    
            return $response;
        } catch (\Exception $e) {
            Log::error('Lỗi lấy sản phẩm thịnh hành: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return "⚠️ Xin lỗi, không thể lấy danh sách sản phẩm nổi bật lúc này. Bạn muốn tìm sản phẩm theo tiêu chí khác không?";
        }
    }
}
