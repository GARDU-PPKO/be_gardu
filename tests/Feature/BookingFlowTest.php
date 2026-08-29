<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use App\Models\PaketWisataTier;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BookingFlowTest extends TestCase
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

    private function validPayload(PaketWisata $paket): array
    {
        return [
            'package_id' => $paket->id,
            'customer_name' => 'Budi Santoso',
            'phone' => '6281234567890',
            'email' => 'budi@gmail.com',
            'date' => now()->addDays(2)->toDateString(),
            'session_time' => 'Pagi (08.00 - 11.00)',
            'participants' => 2,
            'notes' => 'Tidak ada alergi',
        ];
    }

    private function createBooking(array $overrides = []): Booking
    {
        $paket = $this->makePackage();
        $this->makeSession();

        $payload = array_merge($this->validPayload($paket), $overrides);

        $response = $this->postJson('/api/bookings', $payload);
        $response->assertCreated();

        return Booking::where('booking_code', $response->json('data.kode_booking'))->firstOrFail();
    }

    public function test_home_mengembalikan_semua_seksi(): void
    {
        $response = $this->getJson('/api/home');

        $response->assertOk()
            ->assertJsonPath('meta.message', 'Success retrieving home data')
            ->assertJsonStructure(['data' => [
                'settings', 'village_stats', 'dusun', 'tour_packages', 'umkm_products', 'budaya',
            ]]);
    }

    public function test_booking_sessions_mengembalikan_label_lengkap(): void
    {
        $paket = $this->makePackage();
        $this->makeSession();

        $response = $this->getJson('/api/booking-sessions?tanggal=' . now()->addDays(2)->toDateString() . '&package_id=' . $paket->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sesi', 'Pagi (08.00 - 11.00)')
            ->assertJsonPath('data.0.package_id', (string) $paket->id)
            ->assertJsonPath('data.0.kuota', 30)
            ->assertJsonPath('data.0.sisa_kuota', 30)
            ->assertJsonPath('data.0.is_active', true);
    }

    public function test_store_lalu_post_duplikat_mengembalikan_409(): void
    {
        $paket = $this->makePackage();
        $this->makeSession();
        $payload = $this->validPayload($paket);

        $this->postJson('/api/bookings', $payload)->assertCreated();
        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(409)
            ->assertJsonPath('meta.success', false)
            ->assertJsonStructure(['data' => ['kode_booking']]);
    }

    public function test_check_mencari_by_kode(): void
    {
        $booking = $this->createBooking();

        $this->getJson('/api/bookings/check?kode=' . $booking->booking_code)
            ->assertOk()
            ->assertJsonPath('data.kode_booking', $booking->booking_code)
            ->assertJsonPath('data.nama_pemesan', 'Budi Santoso')
            ->assertJsonPath('data.status', 'pending_payment');
    }

    public function test_check_mencari_by_phone(): void
    {
        $booking = $this->createBooking();

        $this->getJson('/api/bookings/check?phone=6281234567890')
            ->assertOk()
            ->assertJsonPath('data.kode_booking', $booking->booking_code);
    }

    public function test_update_data_diri(): void
    {
        $booking = $this->createBooking();

        $this->patchJson("/api/bookings/{$booking->booking_code}", [
            'customer_name' => 'Budi Update',
            'kontak_darurat' => '628111222333',
        ])->assertOk()
            ->assertJsonPath('data.nama_pemesan', 'Budi Update')
            ->assertJsonPath('data.kontak_darurat', '628111222333');
    }

    public function test_cancel_booking(): void
    {
        $booking = $this->createBooking();

        $this->patchJson("/api/bookings/{$booking->booking_code}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(Booking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    public function test_detail_booking_rejected_mengembalikan_alasan(): void
    {
        $booking = $this->createBooking();
        $booking->update([
            'status' => Booking::STATUS_REJECTED,
            'rejected_reason' => 'Bukti tidak jelas/buram',
            'verified_at' => now(),
        ]);

        $response = $this->getJson("/api/bookings/{$booking->booking_code}");

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejected_reason', 'Bukti tidak jelas/buram');

        $this->assertNotNull($response->json('data.rejected_at'));
    }

    public function test_upload_bukti_ditolak_untuk_booking_rejected(): void
    {
        $booking = $this->createBooking();
        $booking->update(['status' => Booking::STATUS_REJECTED]);

        $this->postJson("/api/bookings/{$booking->booking_code}/bukti", [
            'bukti_bayar' => UploadedFile::fake()->create('bukti.pdf', 1024),
        ])->assertStatus(422);
    }

    public function test_update_data_diri_ditolak_untuk_booking_rejected(): void
    {
        $booking = $this->createBooking();
        $booking->update(['status' => Booking::STATUS_REJECTED]);

        $this->patchJson("/api/bookings/{$booking->booking_code}", [
            'customer_name' => 'Nama Baru',
        ])->assertStatus(422);
    }

    public function test_resend_wa_hanya_untuk_pending_payment(): void
    {
        $booking = $this->createBooking();
        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

        $this->postJson("/api/bookings/{$booking->booking_code}/resend-wa")
            ->assertStatus(422);

        $booking->update(['status' => Booking::STATUS_PENDING_PAYMENT]);
        $this->postJson("/api/bookings/{$booking->booking_code}/resend-wa")
            ->assertOk()
            ->assertJsonPath('data.kode_booking', $booking->booking_code);
    }

    public function test_history_berdasarkan_phone(): void
    {
        $this->createBooking();
        $this->createBooking(['customer_name' => 'Budi Kedua']);

        $response = $this->getJson('/api/bookings?phone=6281234567890');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama_pemesan', 'Budi Kedua')
            ->assertJsonStructure(['data' => ['*' => [
                'kode_booking', 'tanggal', 'sesi', 'total_harga', 'status',
            ]], 'pagination' => ['current_page', 'per_page', 'total', 'last_page']]);
    }

    public function test_upload_bukti_pembayaran(): void
    {
        $booking = $this->createBooking();

        $response = $this->postJson("/api/bookings/{$booking->booking_code}/bukti", [
            'bukti_bayar' => UploadedFile::fake()->create('bukti.pdf', 1024),
            'nominal_transfer' => 200000,
            'metode_pembayaran' => 'Transfer Bank',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'pending_verify')
            ->assertJsonPath('meta.message', 'Payment proof uploaded');

        $this->assertNotNull($response->json('data.bukti_bayar'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_PENDING_VERIFY, $booking->status);
        $this->assertNotNull($booking->bukti_pembayaran_path);

        $this->get($response->json('data.bukti_bayar'))
            ->assertOk();
    }

    public function test_show_bukti_untuk_booking_tanpa_bukti_404(): void
    {
        $booking = $this->createBooking();

        $this->get("/api/bookings/{$booking->booking_code}/bukti")
            ->assertNotFound();
    }

    public function test_command_booking_expire_stale(): void
    {
        $booking = $this->createBooking();
        $booking->update([
            'expired_at' => now()->subMinutes(5),
        ]);

        $this->artisan('booking:expire-stale')
            ->expectsOutputToContain('1 booking di-expire.')
            ->assertExitCode(0);

        $this->assertSame(Booking::STATUS_EXPIRED, $booking->fresh()->status);
        $this->assertDatabaseHas('booking_logs', [
            'booking_id' => $booking->id,
            'action' => 'expired',
        ]);
    }

    public function test_command_booking_expire_stale_tanpa_data(): void
    {
        $this->artisan('booking:expire-stale')
            ->expectsOutputToContain('Tidak ada booking menggantung.')
            ->assertExitCode(0);
    }

    public function test_detail_mengandung_payment_info(): void
    {
        Setting::setValue('rekening_bank', 'BCA');
        Setting::setValue('rekening_no', '1234567890');
        Setting::setValue('rekening_atas_nama', 'Desa Gardu');
        Setting::setValue('qris_image', '/storage/qris.png');

        $booking = $this->createBooking();

        $this->getJson("/api/bookings/{$booking->booking_code}")
            ->assertOk()
            ->assertJsonPath('data.payment_info.bank', 'BCA')
            ->assertJsonPath('data.payment_info.nomor_rekening', '1234567890')
            ->assertJsonPath('data.payment_info.atas_nama', 'Desa Gardu')
            ->assertJsonPath('data.payment_info.qris_image', '/storage/qris.png')
            ->assertJsonPath('data.payment_info.batas_waktu_jam', 24);
    }

    public function test_admin_dapat_export_booking_ke_excel(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->createBooking();

        $response = $this->actingAs($user)->get(route('admin.bookings.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
