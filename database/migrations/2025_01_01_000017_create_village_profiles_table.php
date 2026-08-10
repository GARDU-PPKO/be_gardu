<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_profiles', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['sejarah', 'visi', 'misi', 'pemerintahan', 'lainnya'])->default('lainnya');
            $table->string('judul', 150);
            $table->text('konten');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('tipe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_profiles');
    }
};
