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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('discount_type')->default('percent'); // 'percent' or 'fixed'
            $table->integer('discount_value')->default(0);
            $table->integer('min_order_value')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('usage_limit')->default(0);
            $table->integer('used_count')->default(0);
            $table->string('status')->default('active'); // 'active', 'inactive', 'expired'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
