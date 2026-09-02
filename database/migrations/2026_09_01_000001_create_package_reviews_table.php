<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_wisata_id')->constrained('paket_wisata')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('nama_pengulas', 100);
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->text('komentar');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['paket_wisata_id', 'is_visible']);
            $table->index('booking_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('review_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('review_invitation_sent_at')->nullable()->after('review_token');
            $table->timestamp('reviewed_at')->nullable()->after('review_invitation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_reviews');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['review_token', 'review_invitation_sent_at', 'reviewed_at']);
        });
    }
};
