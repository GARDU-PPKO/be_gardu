<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_profile', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['sejarah', 'visi', 'misi', 'pemerintahan']);
            $table->string('judul', 200);
            $table->longText('konten');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_profile');
    }
};
