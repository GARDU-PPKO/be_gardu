<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dusun', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('rw', 10);
            $table->integer('jumlah_rt');
            $table->integer('jumlah_penduduk');
            $table->string('luas_wilayah', 50);
            $table->text('deskripsi');
            $table->text('detail');
            $table->string('hero_img', 255);
            $table->string('thumbnail', 255);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dusun');
    }
};
