<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budaya', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->string('kategori', 100);
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->integer('span_grid')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budaya');
    }
};
