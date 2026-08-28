<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkm_products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('harga');
            $table->string('sku', 50)->nullable()->unique()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('umkm_products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['stock', 'sku']);
        });
    }
};
