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
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('product_id'); // Foreign Key to products table
            $table->unsignedBigInteger('user_id'); // Foreign Key to users table
            $table->unsignedBigInteger('parent_id')->nullable(); // For replies (nullable)
            $table->text('content'); // Comment content
            $table->tinyInteger('rating')->nullable()->comment('Rating from 1-5'); // Rating
            $table->unsignedInteger('likes')->default(0); // Number of likes
            $table->enum('status', ['visible', 'hidden', 'reported'])->default('visible'); // Status
            $table->timestamps(); // Created at and updated at

            // Foreign Key Constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
