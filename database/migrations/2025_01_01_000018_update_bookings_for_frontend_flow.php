<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->after('no_whatsapp');
            $table->string('alamat', 255)->nullable()->change();
            $table->string('kontak_darurat_nama', 100)->nullable()->change();
            $table->string('kontak_darurat_telp', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->string('alamat', 255)->nullable(false)->change();
            $table->string('kontak_darurat_nama', 100)->nullable(false)->change();
            $table->string('kontak_darurat_telp', 20)->nullable(false)->change();
        });
    }
};
