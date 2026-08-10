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
            $table->string('booking_code', 20)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('no_whatsapp', 20);
            $table->string('alamat', 255)->nullable();
            $table->string('kontak_darurat_nama', 100)->nullable();
            $table->string('kontak_darurat_telp', 20)->nullable();
            $table->text('notes')->nullable();
            $table->integer('jumlah_peserta')->default(1);
            $table->date('tanggal_kunjungan');
            $table->foreignId('paket_wisata_id')->constrained('paket_wisata');
            $table->string('sesi', 50);
            $table->decimal('total_harga', 12, 2);
            $table->string('bukti_pembayaran_path', 255)->nullable();
            $table->decimal('nominal_transfer', 12, 2)->nullable();
            $table->string('metode_pembayaran', 50)->nullable();
            $table->enum('status', [
                'PENDING_PAYMENT',
                'PENDING_VERIFY',
                'CONFIRMED',
                'REJECTED',
                'EXPIRED',
                'COMPLETED',
                'CANCELLED',
            ])->default('PENDING_PAYMENT');
            $table->string('rejected_reason', 255)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('raw_wa_text')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tanggal_kunjungan');
            $table->index('paket_wisata_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
