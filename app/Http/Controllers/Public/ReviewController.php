<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\PackageReview;
use App\Models\PaketWisata;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ulasan Paket Wisata')]
class ReviewController extends Controller
{
    #[Endpoint('Verifikasi Token Ulasan')]
    #[PathParameter('token', description: 'Token unik ulasan dari WhatsApp', example: 'a1b2c3d4e5f6...')]
    public function checkToken(string $token): JsonResponse
    {
        $booking = Booking::with(['paketWisata', 'review'])->where('review_token', $token)->first();

        if (! $booking) {
            return ApiResponse::error('Tautan ulasan tidak valid atau sudah tidak berlaku.', 404);
        }

        $package = $booking->paketWisata;
        $hasReviewed = $booking->reviewed_at !== null || $booking->review !== null;

        $data = [
            'booking_code' => $booking->booking_code,
            'customer_name' => $booking->nama_lengkap,
            'tanggal_kunjungan' => $booking->tanggal_kunjungan ? $booking->tanggal_kunjungan->format('Y-m-d') : null,
            'tanggal_kunjungan_formatted' => $booking->tanggal_kunjungan ? $booking->tanggal_kunjungan->translatedFormat('d F Y') : '-',
            'sesi' => $booking->sesi,
            'package' => $package ? [
                'id' => $package->id,
                'nama' => $package->nama,
                'gambar' => $package->gambar,
                'durasi' => $package->durasi,
                'tag' => $package->tag,
            ] : null,
            'has_reviewed' => $hasReviewed,
            'review' => $booking->review ? [
                'id' => $booking->review->id,
                'rating' => $booking->review->rating,
                'komentar' => $booking->review->komentar,
                'created_at' => $booking->review->created_at?->toISOString(),
            ] : null,
        ];

        return ApiResponse::success($data, 'Data booking ulasan berhasil ditemukan.');
    }

    #[Endpoint('Kirim Ulasan')]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|min:3|max:2000',
            'nama_pengulas' => 'nullable|string|max:100',
        ], [
            'rating.required' => 'Rating bintang wajib dipilih (1-5 bintang).',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'komentar.required' => 'Komentar ulasan wajib diisi.',
            'komentar.min' => 'Komentar ulasan minimal 3 karakter.',
        ]);

        $booking = Booking::with('paketWisata')->where('review_token', $validated['token'])->first();

        if (! $booking) {
            return ApiResponse::error('Tautan ulasan tidak valid atau sudah kadaluarsa.', 404);
        }

        if ($booking->reviewed_at !== null || $booking->review()->exists()) {
            return ApiResponse::error('Ulasan untuk kunjungan ini sudah pernah dikirim sebelumnya. Terima kasih!', 400);
        }

        $namaPengulas = ! empty(trim($validated['nama_pengulas'] ?? ''))
            ? trim($validated['nama_pengulas'])
            : $booking->nama_lengkap;

        $review = PackageReview::create([
            'paket_wisata_id' => $booking->paket_wisata_id,
            'booking_id' => $booking->id,
            'nama_pengulas' => $namaPengulas,
            'rating' => (int) $validated['rating'],
            'komentar' => trim($validated['komentar']),
            'is_visible' => true,
        ]);

        $booking->update([
            'reviewed_at' => now(),
            'status' => Booking::STATUS_COMPLETED,
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => null,
            'action' => 'reviewed',
            'detail' => "Pengunjung {$namaPengulas} mengirimkan ulasan (Rating: {$review->rating}/5).",
            'created_at' => now(),
        ]);

        return ApiResponse::success([
            'id' => $review->id,
            'rating' => $review->rating,
            'nama_pengulas' => $review->nama_pengulas,
            'komentar' => $review->komentar,
            'created_at' => $review->created_at?->toISOString(),
        ], 'Terima kasih! Ulasan Anda berhasil dikirim dan tersimpan.', 201);
    }

    #[Endpoint('Daftar Ulasan per Paket Wisata')]
    #[PathParameter('package_id', description: 'ID Paket Wisata', example: '1')]
    public function indexByPackage(int $packageId): JsonResponse
    {
        $package = PaketWisata::where('aktif', true)->findOrFail($packageId);

        $reviews = PackageReview::where('paket_wisata_id', $package->id)
            ->where('is_visible', true)
            ->latest()
            ->paginate(10);

        $shapedReviews = $reviews->getCollection()->map(fn (PackageReview $r) => [
            'id' => $r->id,
            'nama_pengulas' => $r->nama_pengulas,
            'rating' => $r->rating,
            'komentar' => $r->komentar,
            'created_at' => $r->created_at?->toISOString(),
            'tanggal_formatted' => $r->created_at ? $r->created_at->translatedFormat('d M Y') : '-',
        ]);

        $ratingAvg = $package->rating_avg;
        $reviewsCount = $package->reviews_count;

        return response()->json([
            'meta' => [
                'success' => true,
                'status_code' => '200',
                'message' => 'Success retrieving package reviews',
            ],
            'data' => [
                'rating_avg' => $ratingAvg,
                'reviews_count' => $reviewsCount,
                'reviews' => $shapedReviews,
            ],
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
                'from' => $reviews->firstItem() ?? 0,
                'to' => $reviews->lastItem() ?? 0,
            ],
        ]);
    }
}
