<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\PackageReview;
use App\Models\PaketWisata;
use App\Models\PaketWisataTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createPackage(): PaketWisata
    {
        $package = PaketWisata::create([
            'nama' => 'GEMPI Adventure',
            'kategori' => 'tubing',
            'tipe_harga' => 'per_orang_tier',
            'aktif' => true,
        ]);
        PaketWisataTier::create(['paket_id' => $package->id, 'min_peserta' => 5, 'harga_per_orang' => 110000]);

        return $package;
    }

    private function createBooking(PaketWisata $package): Booking
    {
        return Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => 'Budi Santoso',
            'no_whatsapp' => '6281234567890',
            'alamat' => 'Semarang',
            'jumlah_peserta' => 5,
            'tanggal_kunjungan' => now()->subDay()->toDateString(),
            'paket_wisata_id' => $package->id,
            'sesi' => 'Pagi',
            'total_harga' => 550000,
            'status' => Booking::STATUS_CONFIRMED,
            'review_token' => Booking::generateUniqueReviewToken(),
        ]);
    }

    public function test_can_check_valid_review_token(): void
    {
        $package = $this->createPackage();
        $booking = $this->createBooking($package);

        $response = $this->getJson("/api/reviews/check/{$booking->review_token}");

        $response->assertOk()
            ->assertJsonPath('data.booking_code', $booking->booking_code)
            ->assertJsonPath('data.customer_name', 'Budi Santoso')
            ->assertJsonPath('data.package.nama', 'GEMPI Adventure')
            ->assertJsonPath('data.has_reviewed', false);
    }

    public function test_invalid_review_token_returns_404(): void
    {
        $response = $this->getJson('/api/reviews/check/invalid-random-token-123');

        $response->assertStatus(404);
    }

    public function test_can_submit_review_successfully(): void
    {
        $package = $this->createPackage();
        $booking = $this->createBooking($package);

        $payload = [
            'token' => $booking->review_token,
            'rating' => 5,
            'komentar' => 'Pengalaman yang sangat menyenangkan bersama keluarga!',
            'nama_pengulas' => 'Budi Santoso',
        ];

        $response = $this->postJson('/api/reviews', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.nama_pengulas', 'Budi Santoso');

        $this->assertDatabaseHas('package_reviews', [
            'paket_wisata_id' => $package->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'nama_pengulas' => 'Budi Santoso',
        ]);

        $booking->refresh();
        $this->assertNotNull($booking->reviewed_at);
        $this->assertEquals(Booking::STATUS_COMPLETED, $booking->status);
    }

    public function test_cannot_submit_review_twice_for_same_booking(): void
    {
        $package = $this->createPackage();
        $booking = $this->createBooking($package);

        $payload = [
            'token' => $booking->review_token,
            'rating' => 5,
            'komentar' => 'Ulasan pertama.',
            'nama_pengulas' => 'Budi Santoso',
        ];

        $this->postJson('/api/reviews', $payload)->assertCreated();

        // Second attempt
        $secondResponse = $this->postJson('/api/reviews', [
            'token' => $booking->review_token,
            'rating' => 4,
            'komentar' => 'Ulasan kedua.',
        ]);

        $secondResponse->assertStatus(400);
    }

    public function test_tour_packages_api_returns_rating_and_reviews(): void
    {
        $package = $this->createPackage();
        PackageReview::create([
            'paket_wisata_id' => $package->id,
            'nama_pengulas' => 'Reviewer 1',
            'rating' => 5,
            'komentar' => 'Bagus sekali!',
            'is_visible' => true,
        ]);
        PackageReview::create([
            'paket_wisata_id' => $package->id,
            'nama_pengulas' => 'Reviewer 2',
            'rating' => 4,
            'komentar' => 'Cukup memuaskan.',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/api/tour-packages');

        $response->assertOk()
            ->assertJsonPath('data.0.rating_avg', 4.5)
            ->assertJsonPath('data.0.reviews_count', 2);
    }

    public function test_admin_can_trigger_send_review_wa(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@getas.desa',
            'password' => bcrypt('password'),
            'nama' => 'Admin',
            'role' => 'admin',
        ]);

        $package = $this->createPackage();
        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => 'Pengunjung Tes',
            'no_whatsapp' => '08123456789',
            'tanggal_kunjungan' => now()->toDateString(),
            'paket_wisata_id' => $package->id,
            'sesi' => 'Pagi',
            'total_harga' => 110000,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($admin)
            ->post("/admin/bookings/{$booking->id}/send-review-wa");

        $response->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->review_token);
        $this->assertNotNull($booking->review_invitation_sent_at);
        $this->assertEquals(Booking::STATUS_COMPLETED, $booking->status);
    }

    public function test_automatic_review_request_sent_for_completed_visits(): void
    {
        $package = $this->createPackage();

        // Booking yesterday (past date)
        $bookingPast = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => 'Pengunjung Kemarin',
            'no_whatsapp' => '08123456788',
            'tanggal_kunjungan' => now()->subDay()->toDateString(),
            'paket_wisata_id' => $package->id,
            'sesi' => 'Pagi',
            'total_harga' => 110000,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $this->artisan('booking:send-review-requests')
            ->expectsOutputToContain('Berhasil mengirim 1 link ulasan')
            ->assertExitCode(0);

        $bookingPast->refresh();
        $this->assertNotNull($bookingPast->review_token);
        $this->assertNotNull($bookingPast->review_invitation_sent_at);
        $this->assertEquals(Booking::STATUS_COMPLETED, $bookingPast->status);
    }
}
