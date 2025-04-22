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
       
        Schema::create('comment_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id'); // Khóa ngoại liên kết với bảng comments
            $table->string('media_url');             // Đường dẫn file
            $table->string('media_type');            // Loại file (image/video)
            $table->timestamps();
    
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_media');
    }
};
