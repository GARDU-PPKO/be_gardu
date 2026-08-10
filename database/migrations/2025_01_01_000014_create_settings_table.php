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
            $table->text('value');
            $table->string('deskripsi', 255)->nullable();
            $table->timestamps();
        });

        $items = [
            ['key' => 'wa_admin', 'value' => '6287825520140', 'deskripsi' => 'Nomor WhatsApp admin'],
            ['key' => 'nama_desa', 'value' => 'Desa Getas', 'deskripsi' => 'Nama desa'],
            ['key' => 'alamat_desa', 'value' => 'Jl. Raya Getas No. 1, Kec. Singorojo, Kab. Kendal 51382', 'deskripsi' => 'Alamat desa'],
            ['key' => 'fonnte_token', 'value' => 'KM65J2AcX5jekDGYqRFG', 'deskripsi' => 'Token API Fonnte'],
            ['key' => 'rekening_bank', 'value' => 'BNI 123456789 a.n. Desa Getas', 'deskripsi' => 'Informasi rekening untuk pembayaran'],
            ['key' => 'fe_url', 'value' => 'http://localhost:5713', 'deskripsi' => 'URL frontend untuk link upload bukti di WA'],
            ['key' => 'email_desa', 'value' => 'desagetas@kendalkab.go.id', 'deskripsi' => 'Email desa'],
            ['key' => 'jam_pelayanan', 'value' => 'Senin–Jumat: 08.00–15.00 WIB', 'deskripsi' => 'Jam pelayanan'],
            ['key' => 'sosmed_fb', 'value' => 'https://facebook.com/desagetas', 'deskripsi' => 'URL Facebook desa'],
            ['key' => 'sosmed_ig', 'value' => 'https://instagram.com/desagetas', 'deskripsi' => 'URL Instagram desa'],
            ['key' => 'sosmed_yt', 'value' => 'https://youtube.com/@desagetas', 'deskripsi' => 'URL YouTube desa'],
            ['key' => 'sosmed_web', 'value' => 'https://desagetas.id', 'deskripsi' => 'URL Website desa'],
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
