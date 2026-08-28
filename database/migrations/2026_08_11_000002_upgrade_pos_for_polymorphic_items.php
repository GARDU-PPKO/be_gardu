<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->string('customer_name', 100)->nullable()->after('user_id');
        });

        Schema::table('pos_transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            $table->string('item_type', 50)->nullable()->after('transaction_id');
            $table->string('item_id', 36)->nullable()->after('item_type');
            $table->foreignId('booking_id')->nullable()->after('item_id')
                ->constrained('bookings')->nullOnDelete();

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pos_transaction_items', function (Blueprint $table) {
            $table->dropIndex(['item_type', 'item_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['item_type', 'item_id', 'booking_id']);

            $table->foreignId('product_id')->nullable()->after('transaction_id')
                ->constrained('pos_products')->nullOnDelete();
        });

        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};
