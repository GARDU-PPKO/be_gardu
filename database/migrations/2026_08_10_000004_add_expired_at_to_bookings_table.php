<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'expired_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->timestamp('expired_at')->nullable()->after('status');
            });
        }

        DB::table('bookings')
            ->whereNull('expired_at')
            ->orderBy('id')
            ->select('id', 'created_at')
            ->each(function ($row) {
                if (! $row->created_at) {
                    return;
                }

                DB::table('bookings')->where('id', $row->id)->update([
                    'expired_at' => \Illuminate\Support\Carbon::parse($row->created_at)->addDay(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
};
