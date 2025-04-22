<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductPic;
use App\Models\VariantAttribute;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Comment;
use App\Models\CommentMedia;
use App\Models\CommentLike;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Favorite;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->paginate(5);
        return view('admin.products.product_list', compact('products'));
    }

    public function shop(Request $request)
    {
        // Khởi tạo query với bảng Product
        $query = Product::query();

        // Lọc theo danh mục (liên kết qua bảng trung gian product_category)
        if ($request->has('category') && $request->category) {
            $slug = $request->category;

            // Lấy tất cả ID của danh mục cha và các danh mục con
            $categoryIds = Category::where('slug', $slug)
                ->orWhere('parent_id', function ($q) use ($slug) {
                    $q->select('id')->from('categories')->where('slug', $slug);
                })
                ->pluck('id');

            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        // Lọc theo hãng
        if ($request->has('brand') && $request->brand) {
            $brands = explode(',', $request->brand);
            $query->whereIn('brand_id', $brands);
        }

       // Lọc theo thuộc tính (thể loại)
       if ($request->has('attribute') && $request->attribute) {
        $selectedAttributes = explode(',', $request->attribute); // Chuyển từ chuỗi thành mảng
    
        $query->whereHas('productVariants.attributes.value', function ($q) use ($selectedAttributes) {
            $q->whereIn('attribute_values.id', $selectedAttributes);
        });
    }

        // Lọc theo khoảng giá (liên kết qua bảng variant)
        if ($request->has('price') && $request->price) {
            $priceRange = explode('-', $request->price);
        
            if (count($priceRange) == 2) {
                // Filter products within a price range
                $query->whereHas('productVariants', function ($q) use ($priceRange) {
                    $q->where(function ($query) use ($priceRange) {
                        $query->whereBetween('sale_price', [$priceRange[0], $priceRange[1]])
                              ->orWhere(function ($subQuery) use ($priceRange) {
                                  $subQuery->whereNull('sale_price')
                                           ->whereBetween('price', [$priceRange[0], $priceRange[1]]);
                              });
                    });
                });
            } elseif (strpos($request->price, '-') === 0) {
                // Filter products with price greater than or equal to the lower bound
                $query->whereHas('productVariants', function ($q) use ($priceRange) {
                    $q->where(function ($query) use ($priceRange) {
                        $query->where('sale_price', '>=', ltrim($priceRange[1], '-'))
                              ->orWhere(function ($subQuery) use ($priceRange) {
                                  $subQuery->whereNull('sale_price')
                                           ->where('price', '>=', ltrim($priceRange[1], '-'));
                              });
                    });
                });
            } elseif (strpos($request->price, '-') === strlen($request->price) - 1) {
                // Filter products with price less than or equal to the upper bound
                $query->whereHas('productVariants', function ($q) use ($priceRange) {
                    $q->where(function ($query) use ($priceRange) {
                        $query->where('sale_price', '<=', rtrim($priceRange[0], '-'))
                              ->orWhere(function ($subQuery) use ($priceRange) {
                                  $subQuery->whereNull('sale_price')
                                           ->where('price', '<=', rtrim($priceRange[0], '-'));
                              });
                    });
                });
            }
        }
        // tìm theo tên sanr phẩm
        if ($request->has('name') && $request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        // Danh sách khoảng giá để hiển thị trong giao diện
        $priceRanges = [
            ['min' => 0, 'max' => 100000, 'label' => 'Dưới 100.000'],
            ['min' => 100000, 'max' => 200000, 'label' => '100.000 - 200.000'],
            ['min' => 200000, 'max' => 300000, 'label' => '200.000 - 300.000'],
            ['min' => 300000, 'max' => 500000, 'label' => '300.000 - 500.000'],
            ['min' => 500000, 'max' => 1000000, 'label' => '500.000 - 1.000.000'],
            ['min' => 1000000, 'max' => null, 'label' => 'Trên 1.000.000'],
        ];

        // Lấy danh sách sản phẩm sau khi áp dụng bộ lọc
        $products = $query->paginate(12);

        // Lấy dữ liệu danh mục, hãng, màu sắc, kích thước để hiển thị trong giao diện
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $category = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('values')->get();
        $favoriteIds = auth()->check()
        ? auth()->user()->favorites()->pluck('product_id')->toArray()
        : [];
        // Trả về view với dữ liệu
        return view('user.shop', compact('products', 'categories', 'brands', 'attributes', 'priceRanges', 'favoriteIds'));
    }
    public function show($slug)
    {
        // Lấy sản phẩm với các quan hệ cần thiết
        $product = Product::with('productPic', 'brand')->where('slug', $slug)->firstOrFail();

        // Lấy các biến thể của sản phẩm cùng với thuộc tính
        $productVariants = $product->productVariants()->with('attributes')->get();

        // Kiểm tra nếu không có biến thể
        if ($productVariants->isEmpty()) {
            // Lấy bình luận cho sản phẩm
            $comments = Comment::where('product_id', $product->id)
                ->with(['user', 'media', 'like'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Tính điểm đánh giá trung bình
            $averageRating = $comments->avg('rating') ?? 0;

            // Kiểm tra xem người dùng có thể bình luận không
            $canComment = false;
            if (Auth::check()) {
                $canComment = Order::where('user_id', Auth::id())
                    ->where('status', 'completed')
                    ->whereHas('orderItems', function ($query) use ($product) {
                        $query->where('product_id', $product->id);
                    })
                    ->exists();
            }

            // Biến đổi bình luận để thêm totalLikes và likedByUser
            $comments->transform(function ($comment) {
                $comment->totalLikes = $comment->like->count();
                $comment->likedByUser = Auth::check() ? $comment->like->where('user_id', Auth::id())->isNotEmpty() : false;
                return $comment;
            });

            // Nếu không có biến thể, trả về view với giá trị mặc định
            return view('user.detailProduct', [
                'product' => $product,
                'productVariants' => collect([]),
                'minPrice' => 0,
                'minSalePrice' => null,
                'attributeGroups' => [],
                'attributeOrder' => [],
                'comments' => $comments,
                'averageRating' => $averageRating,
                'canComment' => $canComment,
            ]);
        }

        // Tính giá nhỏ nhất và giá giảm nhỏ nhất từ các biến thể
        $minPrice = $productVariants->min('price') ?? 0;
        $minSalePrice = $productVariants->min('sale_price');

        // Nếu tất cả sale_price đều là 0 hoặc null, đặt minSalePrice là null
        if ($minSalePrice === 0 || $minSalePrice === null) {
            $minSalePrice = null;
        }

        // Xử lý các nhóm thuộc tính
        $attributeGroups = $productVariants->pluck('attributes')
            ->flatten()
            ->groupBy('attribute.name')
            ->map(function ($group) {
                return $group->unique('value_id')->map(function ($attribute) {
                    $variantIds = DB::table('variant_attributes')
                        ->where('attribute_id', $attribute->attribute_id)
                        ->where('value_id', $attribute->value_id)
                        ->pluck('product_variant_id')
                        ->all();
                    return [
                        'value_id' => $attribute->value_id,
                        'value' => $attribute->value->value,
                        'variant_ids' => $variantIds,
                    ];
                })->values();
            })->all();

        // Lấy sản phẩm liên quan
        $productRelated = Product::where('brand_id', $product->brand_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        // Lấy thứ tự các thuộc tính
        $attributeOrder = array_keys($attributeGroups);

        // Lấy bình luận cho sản phẩm
        $comments = Comment::where('product_id', $product->id)
            ->with(['user', 'media', 'like'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Tính điểm đánh giá trung bình
        $averageRating = $comments->avg('rating') ?? 0;

        // Kiểm tra xem người dùng có thể bình luận không
        $canComment = false;
        if (Auth::check()) {
            $canComment = Order::where('user_id', Auth::id())
                ->where('status', 'completed')
                ->whereHas('orderItems', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();
        }

        // Biến đổi bình luận để thêm totalLikes và likedByUser
        $comments->transform(function ($comment) {
            $comment->totalLikes = $comment->like->count();
            $comment->likedByUser = Auth::check() ? $comment->like->where('user_id', Auth::id())->isNotEmpty() : false;
            return $comment;
        });
        $favoriteIds = auth()->check()
        ? auth()->user()->favorites()->pluck('product_id')->toArray()
        : [];
        // Trả về view với dữ liệu đã xử lý
        return view('user.detailProduct', compact(
            'product',
            'productVariants',
            'minPrice',
            'minSalePrice',
            'attributeGroups',
            'attributeOrder',
            'productRelated',
            'comments',
            'averageRating',
            'canComment',
            'favoriteIds'
        ));
    }
    public function store()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('values')->get();
        return view('admin.products.product_create', compact('categories', 'attributes', 'brands'));
    }

    public function create(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'required|string',
            'brand_id' => 'required|exists:brands,id',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'variants.*.price' => 'required|numeric',
            'variants.*.sale_price' => 'nullable|numeric',
            'variants.*.cost_price' => 'required|numeric',
            'variants.*.quantity' => 'required|numeric',
            'variants.*.attributes' => 'required|array',
            'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        ]);

        // Handle slug generation if not provided
        if ($request->slug == null) {
            $slug = Str::slug($request->name);
            $request->merge(['slug' => $slug]);
        }

        if (Product::where('slug', $request->slug)->exists()) {
            return redirect()->back()->with('error', 'Slug đã tồn tại!');
        }
        if (Product::where('name', $request->name)->exists()) {
            return redirect()->back()->with('error', 'Tên sản phẩm đã tồn tại!');
        }
        // Create new product record
        $product = Product::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'brand_id' => $request->brand_id,
        ]);

        // Sync categories
        $product->categories()->sync($request->categories);

        // Handle product variants if provided
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                // Generate SKU based on the product name, attributes, and product ID
                $sku = $this->generateSKU($request->name, $request->categories[0], $variant['attributes'], $product->id);

                // Handle variant image upload if provided
                $variantImagePath = null;
                if (isset($variant['image'])) {
                    $variantImagePath = $variant['image']->store('products/variants', 'public');
                }

                // Create product variant
                $productVariant = $product->productVariants()->create([
                    'sku' => $sku,
                    'price' => $variant['price'],
                    'sale_price' => $variant['sale_price'] ?? null,
                    'cost_price' => $variant['cost_price'],
                    'quantityProduct' => $variant['quantity'],
                    'image' => $variantImagePath,
                ]);

                // Create variant attributes
                foreach ($variant['attributes'] as $attribute) {
                    $productVariant->attributes()->create([
                        'attribute_id' => $attribute['attribute_id'],
                        'value_id' => $attribute['value_id'],
                    ]);
                }
            }
        }

        // Handle product images if provided
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                // Store each image and associate with the product
                $imagePath = $image->store('products', 'public');
                $product->productPic()->create([
                    'imagePath' => $imagePath,
                ]);
            }
        }

        // Redirect back with success message
        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function storeFile()
    {
        return view('admin.products.product_createFile');
    }
    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
    ]);

    Excel::import(new ProductsImport, $request->file('file'));

    return back()->with('success', 'Nhập sản phẩm thành công!');
}
    private function generateSKU($productName, $categoryId, $attributes, $productId)
    {
        $category = Category::find($categoryId);
        $categorySlug = $category ? Str::slug($category->nameCategory) : 'uncategorized';
    
        $attributeNames = collect($attributes)->pluck('value_id')->implode('-'); 
    
        $sku = Str::slug($productName . '-' . $categorySlug . '-' . $attributeNames . '-' . $productId);
    
        // Kiểm tra xem SKU có tồn tại không
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = Str::slug($productName . '-' . $categorySlug . '-' . $attributeNames . '-' . $productId . '-' . $counter);
            $counter++;
        }
    
        return $sku;
    }
    
    public function edit($id)
{
    $product = Product::with('productVariants.attributes', 'productPic', 'brand', 'categories')->findOrFail($id);
    $categories = Category::all();
    $brands = Brand::all();
    $attributes = Attribute::with('values')->get(); // Ensure values are loaded
    return view('admin.products.product_edit', compact('product', 'attributes', 'categories', 'brands'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
        'description' => 'required|string',
        'brand_id' => 'required|exists:brands,id',
        'categories' => 'required|array',
        'categories.*' => 'exists:categories,id',
        'variants.*.price' => 'required|numeric',
        'variants.*.sale_price' => 'nullable|numeric',
        'variants.*.cost_price' => 'required|numeric',
        'variants.*.quantity' => 'required|numeric',
        'variants.*.attributes' => 'required|array',
        'variants.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
        'variants.*.attributes.*.value_id' => 'required|exists:attribute_values,id',
        'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
        'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4048',
    ]);

    $product = Product::findOrFail($id);
    if (Product::where('slug', $request->slug)->where('id', '!=', $id)->exists()) {
        return redirect()->back()->with('error', 'Slug đã tồn tại!');
    }
    if (Product::where('name', $request->name)->where('id', '!=', $id)->exists()) {
        return redirect()->back()->with('error', 'Tên sản phẩm đã tồn tại!');
    }

    // Update product details
    $product->update([
        'name' => $request->name,
        'slug' => $request->slug ?? Str::slug($request->name),
        'description' => $request->description,
        'brand_id' => $request->brand_id,
    ]);

    // Sync categories
    $product->categories()->sync($request->categories);

    // Handle removed images
    if ($request->has('removed_images')) {
        foreach ($request->removed_images as $imageId) {
            $image = ProductPic::find($imageId);
            if ($image) {
                Storage::disk('public')->delete($image->imagePath);
                $image->delete();
            }
        }
    }

    // Handle new images
    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $image) {
            $imagePath = $image->store('products', 'public');
            $product->productPic()->create(['imagePath' => $imagePath]);
        }
    }

    // Lấy danh sách ID biến thể từ request
    $variantIdsFromRequest = collect($request->variants)->pluck('id')->filter()->all();

    // Xóa các biến thể không còn trong request
    $product->productVariants()->whereNotIn('id', $variantIdsFromRequest)->delete();

    // Xử lý biến thể
    if ($request->has('variants')) {
        foreach ($request->variants as $variant) {
            $variantData = [
                'price' => $variant['price'],
                'sale_price' => $variant['sale_price'] ?? null,
                'cost_price' => $variant['cost_price'],
                'quantityProduct' => $variant['quantity'],
                'product_id' => $product->id,

            ];
    
            // Nếu có SKU từ form thì sử dụng, nếu không thì tự tạo
            $sku = $variant['sku'] ?? $this->generateSKU($request->name, $variant['attributes'], $product->id);
            $variantData['sku'] = $sku;
    
            // Kiểm tra nếu biến thể đã tồn tại
            if (isset($variant['id']) && $variant['id']) {
                $productVariant = ProductVariant::find($variant['id']);
                if ($productVariant) {
                    // Kiểm tra nếu SKU mới trùng với SKU khác (ngoài chính nó)
                    if (ProductVariant::where('sku', $sku)->where('id', '!=', $productVariant->id)->exists()) {
                        $sku = $this->makeUniqueSKU($sku);
                        $variantData['sku'] = $sku;
                    }
    
                    // Xử lý hình ảnh biến thể khi cập nhật
                    if (isset($variant['image']) && $variant['image']) {
                        $variantData['image'] = $variant['image']->store('products/variants', 'public');
                    } else {
                        $variantData['image'] = $productVariant->image;
                    }                       
     
                    $productVariant->update($variantData);
    
                    // Cập nhật thuộc tính biến thể
                    $productVariant->attributes()->delete();
                    foreach ($variant['attributes'] as $attribute) {
                        $productVariant->attributes()->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'value_id' => $attribute['value_id'],
                        ]);
                    }
                }
            } else {
                // Kiểm tra nếu SKU đã tồn tại trước khi tạo mới
                if (ProductVariant::where('sku', $sku)->exists()) {
                    $sku = $this->makeUniqueSKU($sku);
                    $variantData['sku'] = $sku;
                }
    
                // Xử lý hình ảnh biến thể khi tạo mới
                if (isset($variant['image']) && $variant['image']) {
                    $variantData['image'] = $variant['image']->store('products/variants', 'public');
                } else {
                    // Giữ nguyên ảnh cũ nếu không có ảnh mới được tải lên
                    if (isset($variant['id']) && $variant['id']) {
                        $existingVariant = ProductVariant::find($variant['id']);
                        if ($existingVariant) {
                            $variantData['image'] = $existingVariant->image;
                        }
                    }
                }
                
    
                // Chỉ tạo mới nếu có attributes
                if (!empty($variant['attributes'])) {
                    $newVariant = $product->productVariants()->create($variantData);
                    foreach ($variant['attributes'] as $attribute) {
                        $newVariant->attributes()->create([
                            'attribute_id' => $attribute['attribute_id'],
                            'value_id' => $attribute['value_id'],
                        ]);
                    }
                }
            }
        }
    }

    return redirect()->route('products.index')->with('success', 'Product updated successfully!');
}

// Hàm tạo SKU duy nhất bằng cách thêm hậu tố
private function makeUniqueSKU($sku)
{
    $originalSKU = $sku;
    $counter = 1;
    while (ProductVariant::where('sku', $sku)->exists()) {
        $sku = $originalSKU . '-' . $counter;
        $counter++;
    }
    return $sku;
}
    public function delete($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
