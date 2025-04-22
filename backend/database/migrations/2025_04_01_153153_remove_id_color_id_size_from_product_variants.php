<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropColumn('color_id');

            $table->dropForeign(['size_id']);
            $table->dropColumn('size_id');
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('color_id')->nullable();
            $table->foreign('color_id')->references('idColor')->on('colors')->onDelete('set null');

            $table->unsignedBigInteger('size_id')->nullable();
            $table->foreign('size_id')->references('idSize')->on('sizes')->onDelete('set null');
        });
    }
};
