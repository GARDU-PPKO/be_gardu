<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_stats', function (Blueprint $table) {
            $table->id();
            $table->string('label', 200);
            $table->string('nilai', 50);
            $table->string('satuan', 50)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_stats');
    }
};
