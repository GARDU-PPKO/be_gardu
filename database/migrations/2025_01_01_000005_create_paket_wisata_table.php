<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_wisata', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kategori', 50);
            $table->enum('tipe_harga', ['per_orang_tier', 'per_paket_fixed'])->default('per_orang_tier');
            $table->integer('kapasitas_per_unit')->nullable();
            $table->decimal('harga_paket', 12, 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('fasilitas')->nullable();
            $table->string('gambar', 255)->nullable();
            $table->string('tag', 50)->nullable();
            $table->string('durasi', 100)->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_wisata');
    }
};
