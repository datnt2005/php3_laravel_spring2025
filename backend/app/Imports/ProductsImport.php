<?php
namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariant;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductsImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Kiểm tra 'slug' có tồn tại không
        if (empty($data['slug'])) {
            Log::warning('Slug is missing in row: ', $data);
            return; // bỏ qua dòng lỗi
        }

        // Tạo hoặc lấy sản phẩm
        $product = Product::firstOrCreate(
            ['slug' => trim($data['slug'])],
            [
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'brand_id' => $data['brand_id'] ?? null,
            ]
        );

        // Gắn category
        if (!empty($data['category_ids'])) {
            $categoryIds = explode(',', $data['category_ids']);
            $product->categories()->syncWithoutDetaching($categoryIds);
        }

        // Gắn ảnh chính (chỉ nếu chưa có)
        if ($product->productPic()->count() == 0) {
            for ($i = 1; $i <= 5; $i++) {
                $imgKey = "image_$i";
                if (!empty($data[$imgKey])) {
                    $imagePath = 'products/' . trim($data[$imgKey]);
        
                    // Kiểm tra nếu là URL thì tải về và lưu vào storage
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        try {
                            $image = file_get_contents($imagePath);
                            if ($image === false) {
                                Log::error('Failed to download image from URL: ' . $imagePath);
                                return;
                            }
        
                            $imageName = basename($imagePath);
                            $imageStoragePath = 'products/' . $imageName;
        
                            // Lưu ảnh vào thư mục lưu trữ sử dụng Storage
                            Storage::disk('public')->put($imageStoragePath, $image);
        
                            $imagePath = $imageStoragePath;
        
                        } catch (\Exception $e) {
                            Log::error('Error downloading image: ' . $e->getMessage());
                            return;
                        }
                    }
        
                    // Lưu ảnh vào database
                    $product->productPic()->create([
                        'imagePath' => $imagePath,
                    ]);
                }
            }
        }

        // Tạo variant
        $variant = $product->productVariants()->create([
            'sku' => uniqid('SKU-'),
            'price' => $data['price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? 0,
            'cost_price' => $data['cost_price'] ?? 0,
            'quantityProduct' => $data['quantity'] ?? 0,
            'image' => 'products/variants/' . ($data['variant_image'] ?? 'default.webp'),
        ]);

        // Gắn thuộc tính
        for ($i = 1; $i <= 5; $i++) {
            $attrId = $data["attribute_{$i}_id"] ?? null;
            $valId = $data["value_{$i}_id"] ?? null;

            if ($attrId && $valId) {
                $variant->attributes()->create([
                    'attribute_id' => $attrId,
                    'value_id' => $valId,
                ]);
            }
        }
    }
}
