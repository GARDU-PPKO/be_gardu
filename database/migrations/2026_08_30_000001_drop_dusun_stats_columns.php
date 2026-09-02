<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dusun', function (Blueprint $table) {
            $table->dropColumn(['jumlah_rt', 'jumlah_penduduk', 'luas_wilayah']);
        });
    }

    public function down(): void
    {
        Schema::table('dusun', function (Blueprint $table) {
            $table->integer('jumlah_rt')->nullable();
            $table->integer('jumlah_penduduk')->nullable();
            $table->string('luas_wilayah', 50)->nullable();
        });
    }
};
