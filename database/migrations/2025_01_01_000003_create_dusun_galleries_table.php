<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dusun_galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dusun_id')->constrained('dusun')->cascadeOnDelete();
            $table->string('image_url', 255);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dusun_galleries');
    }
};
