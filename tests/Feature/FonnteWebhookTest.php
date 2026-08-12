<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\FonnteWebhook;
use App\Models\PaketWisata;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FonnteWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected PaketWisata $package;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        $this->package = PaketWisata::create([
            'nama' => 'Tubing Adventure',
            'kategori' => 'Petualangan',
            'tipe_harga' => 'per_paket_fixed',
            'kapasitas_per_unit' => 10,
            'harga_paket' => 75000,
            'deskripsi' => 'Adventure tubing',
            'gambar' => 'https://example.com/img.jpg',
            'aktif' => true,
            'created_by' => $user->id,
        ]);
    }

    public function test_ignores_request_without_phone_or_message(): void
    {
        $response = $this->postJson('/api/fonnte/webhook', []);

        $response->assertJson(['success' => true, 'message' => 'Pesan diterima tetapi tidak diproses.']);
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

        $response->assertJson(['success' => false, 'message' => 'Format data booking tidak lengkap.']);
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

        $response->assertJson(['success' => true, 'message' => 'Booking berhasil dibuat.']);

        $this->assertDatabaseHas('bookings', [
            'nama_lengkap' => 'Budi Santoso',
            'no_whatsapp' => '62812345678',
            'paket_wisata_id' => $this->package->id,
            'jumlah_peserta' => 3,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);
    }
}
