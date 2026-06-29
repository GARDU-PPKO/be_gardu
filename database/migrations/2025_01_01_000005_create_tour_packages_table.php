<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200);
            $table->text('deskripsi');
            $table->decimal('harga', 12, 2);
            $table->enum('satuan', ['orang', 'grup']);
            $table->string('tag', 50)->nullable();
            $table->string('durasi', 100);
            $table->integer('min_participants')->nullable();
            $table->integer('max_participants')->nullable();
            $table->string('gambar', 255);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
