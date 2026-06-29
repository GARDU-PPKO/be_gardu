<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking', 20)->unique();
            $table->string('nama_pemesan', 200);
            $table->string('no_wa_pemesan', 20);
            $table->string('email', 200)->nullable();
            $table->string('kota_asal', 100);
            $table->text('catatan')->nullable();
            $table->foreignId('package_id')->constrained('tour_packages');
            $table->date('tanggal');
            $table->string('sesi', 50);
            $table->integer('jumlah_peserta');
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->string('bukti_bayar', 255)->nullable();
            $table->text('raw_wa_text');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
