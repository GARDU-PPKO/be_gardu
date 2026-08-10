<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use App\Models\PaketWisataTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddOnTest extends TestCase
{
    use RefreshDatabase;

    private function makePackage(): PaketWisata
    {
        $paket = PaketWisata::create([
            'nama' => 'GENTA Explorer',
            'kategori' => 'tubing',
            'tipe_harga' => 'per_orang_tier',
            'aktif' => true,
        ]);
        PaketWisataTier::create(['paket_id' => $paket->id, 'min_peserta' => 1, 'harga_per_orang' => 100000]);

        return $paket;
    }

    private function makeSession(): BookingSession
    {
        return BookingSession::create([
            'sesi' => 'Pagi',
            'jam_mulai' => '08:00',
            'jam_selesai' => '11:00',
            'kuota' => 30,
            'is_active' => true,
        ]);
    }

    public function test_index_menampilkan_add_on_aktif(): void
    {
        AddOn::create(['nama' => 'Makan Siang', 'tipe_harga' => 'per_orang', 'harga' => 25000, 'aktif' => true, 'urutan' => 1]);
        AddOn::create(['nama' => 'Sewa ATV', 'tipe_harga' => 'per_unit', 'harga' => 75000, 'aktif' => true, 'urutan' => 2]);
        AddOn::create(['nama' => 'Add-on Nonaktif', 'tipe_harga' => 'per_unit', 'harga' => 1000, 'aktif' => false]);

        $response = $this->getJson('/api/addons');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama', 'Makan Siang')
            ->assertJsonPath('data.0.harga', 25000)
            ->assertJsonPath('data.0.satuan', 'per orang')
            ->assertJsonPath('data.0.is_free', false)
            ->assertJsonPath('data.0.urutan', 1)
            ->assertJsonMissingPath('data.0.tipe_harga');
    }

    public function test_store_menghitung_total_dengan_add_on(): void
    {
        $paket = $this->makePackage();
        $this->makeSession();
        $makan = AddOn::create(['nama' => 'Makan Siang', 'tipe_harga' => 'per_orang', 'harga' => 25000, 'aktif' => true]);
        $atv = AddOn::create(['nama' => 'Sewa ATV', 'tipe_harga' => 'per_unit', 'harga' => 75000, 'aktif' => true]);

        $response = $this->postJson('/api/bookings', [
            'package_id' => $paket->id,
            'customer_name' => 'Budi Santoso',
            'phone' => '6281234567890',
            'date' => now()->addDays(2)->toDateString(),
            'session_time' => 'Pagi (08.00 - 11.00)',
            'participants' => 3,
            'addons' => [
                ['id' => $makan->id, 'quantity' => 3],
                ['id' => $atv->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_harga', 450000)
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('meta.message', 'Booking successfully created');

        $this->assertNotNull($response->json('data.expired_at'));

        $booking = Booking::where('booking_code', $response->json('data.kode_booking'))->first();
        $this->assertNotNull($booking);
        $this->assertCount(2, $booking->addOns);
        $this->assertEquals(3, $booking->addOns->firstWhere('id', $makan->id)->pivot->qty);
        $this->assertEquals(25000, (float) $booking->addOns->firstWhere('id', $makan->id)->pivot->harga_satuan);
    }

    public function test_store_menolak_add_on_tidak_aktif(): void
    {
        $paket = $this->makePackage();
        $this->makeSession();
        $nonaktif = AddOn::create(['nama' => 'Add-on Nonaktif', 'tipe_harga' => 'per_unit', 'harga' => 1000, 'aktif' => false]);

        $response = $this->postJson('/api/bookings', [
            'package_id' => $paket->id,
            'customer_name' => 'Budi Santoso',
            'phone' => '6281234567890',
            'date' => now()->addDays(2)->toDateString(),
            'session_time' => 'Pagi (08.00 - 11.00)',
            'participants' => 3,
            'addons' => [
                ['id' => $nonaktif->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_show_menampilkan_add_on_booking(): void
    {
        $paket = $this->makePackage();
        $makan = AddOn::create(['nama' => 'Makan Siang', 'tipe_harga' => 'per_orang', 'harga' => 25000, 'aktif' => true]);

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => 'Budi Santoso',
            'no_whatsapp' => '6281234567890',
            'jumlah_peserta' => 2,
            'tanggal_kunjungan' => now()->addDays(2)->toDateString(),
            'paket_wisata_id' => $paket->id,
            'sesi' => 'Pagi',
            'total_harga' => 250000,
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        $booking->addOns()->attach($makan->id, [
            'qty' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);

        $response = $this->getJson("/api/bookings/{$booking->booking_code}");

        $response->assertOk()
            ->assertJsonPath('data.nama_pemesan', 'Budi Santoso')
            ->assertJsonPath('data.package.nama', 'GENTA Explorer')
            ->assertJsonCount(1, 'data.addons')
            ->assertJsonPath('data.addons.0.nama', 'Makan Siang')
            ->assertJsonPath('data.addons.0.quantity', 2)
            ->assertJsonPath('data.addons.0.harga', 25000)
            ->assertJsonPath('data.total_harga', 250000);
    }
}
