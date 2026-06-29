<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkm_products', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200);
            $table->enum('kategori', ['Makanan', 'Kerajinan', 'Pertanian', 'Oleh-Oleh']);
            $table->decimal('harga', 12, 2);
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->string('no_wa_penjual', 20);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm_products');
    }
};
