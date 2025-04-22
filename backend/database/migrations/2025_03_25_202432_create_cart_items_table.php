<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id(); // ID chính của bảng
            $table->unsignedBigInteger('cart_id'); // ID cарт
            $table->unsignedBigInteger('product_id'); // ID sản phẩm
            $table->unsignedBigInteger('product_variant_id'); // ID biến thể sản phẩm
            $table->string('sku')->nullable(); // SKU của sản phẩm (có thể null nếu không cần)
            $table->integer('quantity')->default(1); // Số lượng sản phẩm
            $table->decimal('price', 10, 2); // Giá sản phẩm
            $table->timestamps(); // Tự động thêm created_at và updated_at

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('idVariant')->on('product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};