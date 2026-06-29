<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasIndex('village_profile', 'village_profile_tipe_index')) {
            Schema::table('village_profile', function (Blueprint $table) {
                $table->index('tipe');
            });
        }

        if (!Schema::hasIndex('village_stats', 'village_stats_urutan_index')) {
            Schema::table('village_stats', function (Blueprint $table) {
                $table->index('urutan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('village_profile', function (Blueprint $table) {
            $table->dropIndex(['tipe']);
        });

        Schema::table('village_stats', function (Blueprint $table) {
            $table->dropIndex(['urutan']);
        });
    }
};
