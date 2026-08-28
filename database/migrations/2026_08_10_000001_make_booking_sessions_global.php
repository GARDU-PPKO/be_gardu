<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('booking_sessions', 'paket_wisata_id')) {
            Schema::table('booking_sessions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('paket_wisata_id');
            });
        }

        $times = [
            'Pagi'  => ['08:00:00', '11:00:00'],
            'Siang' => ['11:00:00', '14:00:00'],
            'Sore'  => ['14:00:00', '16:00:00'],
        ];

        foreach ($times as $sesi => [$mulai, $selesai]) {
            DB::table('booking_sessions')
                ->where('sesi', $sesi)
                ->update([
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('booking_sessions', 'paket_wisata_id')) {
            Schema::table('booking_sessions', function (Blueprint $table) {
                $table->foreignId('paket_wisata_id')->nullable()->constrained('paket_wisata')->cascadeOnDelete();
            });
        }
    }
};
