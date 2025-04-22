<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddressesTableToUseIds extends Migration
{
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Xóa các cột cũ
            $table->dropColumn('province');
            $table->dropColumn('district');
            $table->dropColumn('ward');

            // Thêm các cột mới
            $table->integer('province_id')->after('phone');
            $table->integer('district_id')->after('province_id');
            $table->string('ward_code', 10)->nullable()->after('district_id');
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Khôi phục các cột cũ
            $table->string('province');
            $table->string('district');
            $table->string('ward')->nullable();

            // Xóa các cột mới
            $table->dropColumn('province_id');
            $table->dropColumn('district_id');
            $table->dropColumn('ward_code');
        });
    }
}