<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\FonnteWebhook;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FonnteWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected TourPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        $this->package = TourPackage::create([
            'nama' => 'Tubing Adventure',
            'deskripsi' => 'Adventure tubing',
            'harga' => 75000,
            'satuan' => 'orang',
            'durasi' => '2 jam',
            'min_participants' => 1,
            'max_participants' => 10,
            'gambar' => 'https://example.com/img.jpg',
            'is_active' => true,
            'created_by' => 1,
        ]);
    }

    public function test_ignores_request_without_phone_or_message(): void
    {
        $response = $this->postJson('/api/fonnte/webhook', []);

        $response->assertJson(['status' => 'ignored']);
    }

    public function test_creates_webhook_log(): void
    {
        $this->postJson('/api/fonnte/webhook', [
            'phone' => '62812345678',
            'message' => 'Test',
        ]);

        $this->assertDatabaseHas('fonnte_webhooks', [
            'phone' => '62812345678',
            'fonnte_type' => 'incoming',
        ]);
    }

    public function test_returns_invalid_format_when_data_incomplete(): void
    {
        $response = $this->postJson('/api/fonnte/webhook', [
            'phone' => '62812345678',
            'message' => 'Halo, saya mau booking',
        ]);

        $response->assertJson(['status' => 'invalid_format']);
    }

    public function test_creates_booking_from_valid_message(): void
    {
        $message = "Nama: Budi Santoso\n"
            . "No. WA: 62812345678\n"
            . "Paket: Tubing Adventure\n"
            . "Tanggal: 2026-07-15\n"
            . "Sesi: Pagi\n"
            . "Peserta: 3\n"
            . "Total: 225000";

        $response = $this->postJson('/api/fonnte/webhook', [
            'phone' => '62812345678',
            'message' => $message,
        ]);

        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('bookings', [
            'nama_pemesan' => 'Budi Santoso',
            'no_wa_pemesan' => '62812345678',
            'package_id' => $this->package->id,
            'jumlah_peserta' => 3,
            'status' => 'pending',
        ]);
    }
}
