<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageReview;
use App\Models\PaketWisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = PackageReview::with(['paketWisata:id,nama', 'booking:id,booking_code,tanggal_kunjungan']);

        if ($request->filled('package_id') && $request->package_id !== 'all') {
            $query->where('paket_wisata_id', $request->package_id);
        }

        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('visibility') && $request->visibility !== 'all') {
            $query->where('is_visible', $request->visibility === '1');
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();
        $packages = PaketWisata::orderBy('nama')->get(['id', 'nama']);

        $totalReviews = PackageReview::count();
        $avgRating = PackageReview::where('is_visible', true)->avg('rating');

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'packages' => $packages,
            'totalReviews' => $totalReviews,
            'avgRating' => $avgRating ? round($avgRating, 1) : 0,
            'selectedPackage' => $request->package_id ?? 'all',
            'selectedRating' => $request->rating ?? 'all',
            'selectedVisibility' => $request->visibility ?? 'all',
        ]);
    }

    public function toggleVisibility($id): RedirectResponse
    {
        $review = PackageReview::findOrFail($id);
        $review->update(['is_visible' => ! $review->is_visible]);

        $status = $review->is_visible ? 'ditampilkan' : 'disembunyikan';
        return back()->with('success', "Ulasan dari {$review->nama_pengulas} berhasil {$status}.");
    }

    public function destroy($id): RedirectResponse
    {
        $review = PackageReview::findOrFail($id);
        $name = $review->nama_pengulas;
        $review->delete();

        return back()->with('success', "Ulasan dari {$name} berhasil dihapus.");
    }
}
