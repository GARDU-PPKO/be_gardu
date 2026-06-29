<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_package_includes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->string('item', 255);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_package_includes');
    }
};
