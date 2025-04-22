<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liên kết với user
            $table->string('name'); // Tên người nhận
            $table->string('phone'); // Số điện thoại
            $table->string('province'); // Tỉnh/Thành phố
            $table->string('district'); // Quận/Huyện
            $table->string('ward')->nullable(); // Xã/Phường (Có thể không bắt buộc)
            $table->string('detail'); // Địa chỉ cụ thể (Số nhà, đường)
            $table->boolean('is_default')->default(false); // Đánh dấu địa chỉ mặc định
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
};
