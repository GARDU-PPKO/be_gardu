<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budaya_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budaya_id')->constrained('budaya');
            $table->string('nama_acara', 200);
            $table->string('hari', 50);
            $table->string('jam', 50);
            $table->text('deskripsi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budaya_schedules');
    }
};
