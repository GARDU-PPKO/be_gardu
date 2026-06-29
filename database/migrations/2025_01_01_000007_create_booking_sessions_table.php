<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('tour_packages');
            $table->date('tanggal');
            $table->enum('sesi', ['Pagi', 'Siang', 'Sore']);
            $table->integer('kuota');
            $table->integer('terisi')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_sessions');
    }
};
