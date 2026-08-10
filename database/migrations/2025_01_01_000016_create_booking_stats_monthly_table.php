<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_stats_monthly', function (Blueprint $table) {
            $table->id();
            $table->char('year_month', 7)->unique();
            $table->integer('total_booking')->default(0);
            $table->integer('total_confirmed')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->integer('total_pengunjung')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_stats_monthly');
    }
};
