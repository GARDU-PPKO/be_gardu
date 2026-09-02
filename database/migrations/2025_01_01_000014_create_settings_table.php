<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('deskripsi', 255)->nullable();
            $table->timestamps();
        });

        $items = [
            // 🌐 Website & Integrasi
            ['key' => 'fe_url', 'value' => 'http://localhost:5173', 'deskripsi' => 'URL frontend untuk link upload bukti di WA'],
            ['key' => 'ar_url', 'value' => 'https://feby-akliji23.github.io/AR-BETA_V01/', 'deskripsi' => 'URL aplikasi Web AR'],
            ['key' => 'fonnte_token', 'value' => 'KM65J2AcX5jekDGYqRFG', 'deskripsi' => 'Token API Fonnte'],
            ['key' => 'sosmed_ig', 'value' => 'https://instagram.com/desagetas', 'deskripsi' => 'URL Instagram desa'],
            ['key' => 'sosmed_fb', 'value' => 'https://facebook.com/desagetas', 'deskripsi' => 'URL Facebook desa'],
            ['key' => 'sosmed_yt', 'value' => 'https://youtube.com/@desagetas', 'deskripsi' => 'URL YouTube desa'],
            ['key' => 'sosmed_tiktok', 'value' => '', 'deskripsi' => 'URL TikTok desa (opsional)'],
            ['key' => 'hero_image', 'value' => '', 'deskripsi' => 'URL/path gambar banner hero (opsional)'],

            // 💳 Pembayaran & Rekening
            ['key' => 'rekening_bank', 'value' => 'BNI', 'deskripsi' => 'Nama bank untuk pembayaran transfer'],
            ['key' => 'rekening_no', 'value' => '123456789', 'deskripsi' => 'Nomor rekening bank untuk pembayaran'],
            ['key' => 'rekening_atas_nama', 'value' => 'Desa Getas', 'deskripsi' => 'Nama pemilik rekening bank'],
            ['key' => 'qris_image', 'value' => '', 'deskripsi' => 'URL gambar barcode QRIS (alternatif pembayaran transfer)'],

            // 📋 Pengelola & Kebijakan Wisata
            ['key' => 'check_in_time', 'value' => '13.00 WIB', 'deskripsi' => 'Jam kedatangan / check-in'],
            ['key' => 'check_out_time', 'value' => '11.00 WIB', 'deskripsi' => 'Jam kepulangan / check-out'],
            ['key' => 'cancel_policy', 'value' => 'Pembatalan/reschedule maks. 8 jam sebelum kedatangan', 'deskripsi' => 'Kebijakan pembatalan & reschedule'],
            ['key' => 'night_curfew', 'value' => 'Jam malam mulai 22.00 WIB', 'deskripsi' => 'Aturan batas jam malam'],
            ['key' => 'jam_pelayanan', 'value' => 'Senin–Jumat: 08.00–15.00 WIB', 'deskripsi' => 'Jam pelayanan kantor & pengelola'],

            // 🏛️ Informasi Desa & Kontak Admin
            ['key' => 'nama_desa', 'value' => 'Desa Getas', 'deskripsi' => 'Nama desa wisata'],
            ['key' => 'alamat_desa', 'value' => 'Jl. Raya Getas No. 1, Kec. Singorojo, Kab. Kendal 51382', 'deskripsi' => 'Alamat lengkap desa'],
            ['key' => 'email_desa', 'value' => 'desagetas@kendalkab.go.id', 'deskripsi' => 'Email resmi desa'],
            ['key' => 'wa_admin', 'value' => '6287825520140', 'deskripsi' => 'Nomor WhatsApp admin penerima notifikasi'],
        ];

        foreach ($items as $item) {
            Setting::firstOrCreate(['key' => $item['key']], $item);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
