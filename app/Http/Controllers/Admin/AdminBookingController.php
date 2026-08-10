<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', Booking::STATUS_PENDING_VERIFY);
        $valid = [
            'all',
            Booking::STATUS_PENDING_PAYMENT,
            Booking::STATUS_PENDING_VERIFY,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_REJECTED,
            Booking::STATUS_EXPIRED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_CANCELLED,
            'deleted',
        ];

        if (! in_array($status, $valid, true)) {
            $status = Booking::STATUS_PENDING_VERIFY;
        }

        $query = Booking::with('paketWisata:id,nama');

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->where('status', $status)->orderBy('created_at', 'asc');
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'filterStatus' => $status,
        ]);
    }

    public function show($id): View
    {
        return view('admin.bookings.show', [
            'booking' => Booking::withTrashed()
                ->with(['paketWisata:id,nama', 'logs.admin:id,nama'])
                ->findOrFail($id),
        ]);
    }

    public function confirm($id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        abort_unless($booking->status === Booking::STATUS_PENDING_VERIFY, 422);

        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => auth()->id(),
            'action' => 'confirmed',
            'detail' => 'Pembayaran divalidasi dan booking dikonfirmasi.',
            'created_at' => now(),
        ]);

        app(FonnteService::class)->send($booking->no_whatsapp, $this->buildConfirmMessage($booking));

        return back()->with('success', "Booking {$booking->booking_code} dikonfirmasi");
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        abort_unless($booking->status === Booking::STATUS_PENDING_VERIFY, 422);

        $request->validate([
            'rejected_reason' => 'required_without:rejected_reason_custom|string|max:255',
            'rejected_reason_custom' => 'nullable|string|max:255',
        ]);

        $reason = $request->filled('rejected_reason_custom')
            ? $request->rejected_reason_custom
            : $request->rejected_reason;

        $booking->update([
            'status' => Booking::STATUS_REJECTED,
            'rejected_reason' => $reason,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => auth()->id(),
            'action' => 'rejected',
            'detail' => 'Alasan: ' . $reason,
            'created_at' => now(),
        ]);

        app(FonnteService::class)->send($booking->no_whatsapp, $this->buildRejectMessage($booking));

        return back()->with('success', "Booking {$booking->booking_code} ditolak");
    }

    public function destroy($id): RedirectResponse
    {
        Booking::findOrFail($id)->delete();

        return back()->with('success', 'Booking dihapus (soft delete)');
    }

    public function restore($id): RedirectResponse
    {
        Booking::withTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Booking dipulihkan');
    }

    public function showBukti($id): StreamedResponse
    {
        $booking = Booking::withTrashed()->findOrFail($id);

        abort_unless($booking->bukti_pembayaran_path, 404);
        abort_unless(Storage::disk('local')->exists($booking->bukti_pembayaran_path), 404);

        return Storage::disk('local')->response($booking->bukti_pembayaran_path);
    }

    private function buildConfirmMessage(Booking $booking): string
    {
        return "GARDU - Booking TERKONFIRMASI ✅\n"
            . "Kode Booking: {$booking->booking_code}\n"
            . "Nama: {$booking->nama_lengkap}\n"
            . "Paket: {$booking->paketWisata?->nama}\n"
            . "Tanggal: {$booking->tanggal_kunjungan->format('d-m-Y')} ({$booking->sesi})\n"
            . "Peserta: {$booking->jumlah_peserta} orang\n\n"
            . "Simpan pesan ini dan tunjukkan kepada petugas di resepsionis saat tiba di lokasi.\n"
            . "Sampai jumpa di Desa Getas!";
    }

    private function buildRejectMessage(Booking $booking): string
    {
        $feUrl = Setting::getValue('fe_url') ?: url('/');
        $uploadUrl = rtrim($feUrl, '/') . "/booking/upload/{$booking->booking_code}";

        return "GARDU - Bukti Pembayaran DITOLAK\n"
            . "Kode: {$booking->booking_code}\n"
            . "Alasan: {$booking->rejected_reason}\n\n"
            . "Silakan unggah ulang bukti yang benar melalui link berikut:\n{$uploadUrl}";
    }
}
