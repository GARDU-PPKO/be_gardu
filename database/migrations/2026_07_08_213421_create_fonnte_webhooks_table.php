<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fonnte_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->text('attachment')->nullable();
            $table->string('event')->nullable();
            $table->string('fonnte_type')->nullable()->comment('incoming / status');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fonnte_webhooks');
    }
};
