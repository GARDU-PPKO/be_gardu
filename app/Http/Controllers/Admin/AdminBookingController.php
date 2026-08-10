<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\PaketWisata;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
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
                ->with(['paketWisata:id,nama', 'logs.admin:id,nama', 'addOns'])
                ->findOrFail($id),
        ]);
    }

    public function parse(): View
    {
        return view('admin.bookings.parse');
    }

    public function parseText(Request $request): RedirectResponse
    {
        $request->validate([
            'raw_text' => 'required|string',
            'bukti_bayar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $text = $request->raw_text;
        $data = $this->extractBookingData($text);

        $package = PaketWisata::where('nama', 'like', '%' . $data['package_name'] . '%')->first();

        if (! $package) {
            return back()->with('parse_error', 'Paket "' . $data['package_name'] . '" tidak ditemukan. Periksa teks dan coba lagi.')
                ->withInput()->with('parsed_data', $data);
        }

        $bookingCode = Booking::generateBookingCode();

        $buktiBayar = null;
        if ($request->hasFile('bukti_bayar')) {
            $tanggal = $data['tanggal'] ?: now()->format('Y-m-d');
            $filename = $tanggal . '_' . $bookingCode . '.jpg';

            $image = Image::read($request->file('bukti_bayar'));
            $image->scaleDown(width: 1200);

            $image->save(storage_path('app/public/buktibayar/' . $filename));

            $buktiBayar = 'buktibayar/' . $filename;
        }

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'nama_lengkap' => $data['nama'],
            'no_whatsapp' => $data['no_wa'],
            'email' => $data['email'] ?? null,
            'alamat' => $data['kota'] ?? '',
            'notes' => $data['catatan'] ?? null,
            'paket_wisata_id' => $package->id,
            'tanggal_kunjungan' => $data['tanggal'] ?: now()->format('Y-m-d'),
            'sesi' => $data['sesi'] ?: 'Pagi',
            'jumlah_peserta' => $data['jumlah_peserta'] ?: 1,
            'total_harga' => $data['total_harga'] ?: 0,
            'status' => Booking::STATUS_CONFIRMED,
            'bukti_pembayaran_path' => $buktiBayar,
            'raw_wa_text' => $text,
            'created_by' => auth()->id(),
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => auth()->id(),
            'action' => 'confirmed',
            'detail' => 'Booking dibuat manual dari teks WhatsApp.',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking berhasil dibuat! Kode: ' . $bookingCode);
    }

    public function confirm($id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

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
        
        $path = $booking->bukti_pembayaran_path;
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path);
    }

    private function extractBookingData(string $text): array
    {
        $data = [
            'package_name' => '',
            'tanggal' => '',
            'sesi' => 'Pagi',
            'jumlah_peserta' => 1,
            'total_harga' => 0,
            'nama' => '',
            'no_wa' => '',
            'email' => null,
            'kota' => '',
            'catatan' => null,
        ];

        foreach (explode("\n", $text) as $raw) {
            $line = trim(preg_replace('/[^\p{L}\p{N}:.@\s\/-]+/u', ' ', $raw));
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (! $line) continue;

            if (preg_match('/^(?:Paket|Nama\s*Paket|Package|Pack)\s*:\s*(.+)$/iu', $line, $m))
                $data['package_name'] = trim($m[1]);
            elseif (preg_match('/^(?:Tanggal|Tgl|Date|Tangeal)\s*:\s*(.+)$/iu', $line, $m))
                $data['tanggal'] = trim($m[1]);
            elseif (preg_match('/^(?:Sesi|Session|Jam|Waktu)\s*:\s*(.+)$/iu', $line, $m))
                $data['sesi'] = trim($m[1]);
            elseif (preg_match('/^(?:Peserta|Jumlah\s*Peserta|Pax|Orang|Peseta)\s*:\s*(\d+)/iu', $line, $m))
                $data['jumlah_peserta'] = (int) $m[1];
            elseif (preg_match('/^(?:Total|Total\s*Harga|Harga|Price|Biaya)\s*:\s*(?:Rp\.?\s*)?([\d.,]+)/iu', $line, $m))
                $data['total_harga'] = (int) str_replace(['.', ','], '', $m[1]);
            elseif (preg_match('/^(?:Nama|Name|Nama\s*Pemesan)\s*:\s*(.+)$/iu', $line, $m))
                $data['nama'] = trim($m[1]);
            elseif (preg_match('/^(?:WhatsApp|No\.?\s*WA|WA|Phone|Telepon|No\.?\s*HP|HP)\s*:\s*(.+)$/iu', $line, $m))
                $data['no_wa'] = trim($m[1]);
            elseif (preg_match('/^(?:Email|E-mail|Mail|Surel)\s*:\s*(.+)$/iu', $line, $m))
                $data['email'] = trim($m[1]);
            elseif (preg_match('/^(?:Kota|City|Asal|Kota\s*Asal|Domisili)\s*:\s*(.+)$/iu', $line, $m))
                $data['kota'] = trim($m[1]);
            elseif (preg_match('/^(?:Catatan|Note|Pesan|Keterangan)\s*:\s*(.+)$/iu', $line, $m))
                $data['catatan'] = trim($m[1]);
        }

        return $data;
    }

    private function buildConfirmMessage(Booking $booking): string
    {
        return "Halo {$booking->nama_lengkap},\n\n"
            . "Kabar baik! Booking dengan kode *{$booking->booking_code}* telah *dikonfirmasi* ✅\n\n"
            . "📋 Kode Booking: {$booking->booking_code}\n"
            . "🏕️ Paket: {$booking->paketWisata?->nama}\n"
            . "📅 Tanggal: {$booking->tanggal_kunjungan->format('d-m-Y')}\n\n"
            . "Silahkan datang sesuai jadwal booking Anda.\n\n"
            . "Sampai jumpa di Desa Wisata Getas! 🌿";
    }

    private function buildRejectMessage(Booking $booking): string
    {
        return "Halo {$booking->nama_lengkap},\n\n"
            . "Mohon maaf, bukti pembayaran untuk booking dengan kode *{$booking->booking_code}* *tidak dapat kami verifikasi* ❌\n\n"
            . "Alasan: {$booking->rejected_reason}\n\n"
            . "Mohon maaf atas ketidaknyamanannya 🙏";
    }

    public function export()
    {
        $bookings = Booking::with('paketWisata:id,nama')->orderBy('created_at', 'desc')->get();

        $path = tempnam(sys_get_temp_dir(), 'bookings') . '.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues([
            'Kode Booking', 'Nama Pemesan', 'No. WA', 'Email', 'Alamat',
            'Paket', 'Tanggal Kunjungan', 'Sesi', 'Jumlah Peserta', 'Total Harga',
            'Status', 'Catatan', 'Tanggal Booking',
        ]));

        foreach ($bookings as $b) {
            $writer->addRow(Row::fromValues([
                $b->booking_code,
                $b->nama_lengkap,
                $b->no_whatsapp,
                $b->email ?? '',
                $b->alamat ?? '',
                $b->paketWisata?->nama ?? '',
                $b->tanggal_kunjungan ? $b->tanggal_kunjungan->format('Y-m-d') : '',
                $b->sesi,
                $b->jumlah_peserta,
                $b->total_harga,
                $b->status,
                $b->notes ?? '',
                $b->created_at->format('Y-m-d H:i'),
            ]));
        }

        $writer->close();

        return response()->download($path, 'bookings-export-' . now()->format('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }
}
