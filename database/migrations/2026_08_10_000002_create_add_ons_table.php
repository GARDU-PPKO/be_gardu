<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kategori', 50)->nullable();
            $table->enum('tipe_harga', ['per_orang', 'per_unit'])->default('per_unit');
            $table->decimal('harga', 12, 2);
            $table->text('deskripsi')->nullable();
            $table->string('gambar', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('add_ons');
    }
};
