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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id('idVariant');
            $table->string('sku')->unique();
            $table->string('price');
            $table->integer('quantityProduct'); // Nên dùng integer thay vì string
            $table->unsignedBigInteger('color_id'); // Khóa ngoại
            $table->unsignedBigInteger('size_id');
            $table->unsignedBigInteger('product_id');
            $table->string('image');
            $table->timestamps();
        
            // Thêm khóa ngoại đúng với bảng có idColor, idSize
            $table->foreign('color_id')->references('idColor')->on('colors')->onDelete('cascade');
            $table->foreign('size_id')->references('idSize')->on('sizes')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade'); // Giữ nguyên nếu products có id mặc định
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
