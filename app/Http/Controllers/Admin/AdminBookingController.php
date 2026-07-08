<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TourPackage;
use App\Services\FonnteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;

class AdminBookingController extends Controller
{
    public function index(): View
    {
        return view('admin.bookings.index', [
            'bookings' => Booking::with('package:id,nama')->orderBy('created_at', 'desc')->paginate(15),
        ]);
    }

    public function show($id): View
    {
        return view('admin.bookings.show', [
            'booking' => Booking::with('package:id,nama')->findOrFail($id),
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

        $package = TourPackage::where('nama', 'like', '%' . $data['package_name'] . '%')->first();

        if (!$package) {
            return back()->with('parse_error', 'Paket "' . $data['package_name'] . '" tidak ditemukan. Periksa teks dan coba lagi.')
                ->withInput()->with('parsed_data', $data);
        }

        $kodeBooking = 'GB-' . strtoupper(Str::random(8));

        $buktiBayar = null;
        if ($request->hasFile('bukti_bayar')) {
            $tanggal = $data['tanggal'] ?: now()->format('Y-m-d');
            $filename = $tanggal . '_' . $kodeBooking . '.jpg';

            $image = Image::decode($request->file('bukti_bayar'));
            $image->scaleDown(width: 1200);

            $image->save(storage_path('app/public/buktibayar/' . $filename));

            $buktiBayar = '/storage/buktibayar/' . $filename;
        }

        $booking = Booking::create([
            'kode_booking' => $kodeBooking,
            'nama_pemesan' => $data['nama'],
            'no_wa_pemesan' => $data['no_wa'],
            'email' => $data['email'] ?? null,
            'kota_asal' => $data['kota'] ?? '',
            'catatan' => $data['catatan'] ?? null,
            'package_id' => $package->id,
            'tanggal' => $data['tanggal'],
            'sesi' => $data['sesi'],
            'jumlah_peserta' => $data['jumlah_peserta'],
            'total_harga' => $data['total_harga'],
            'status' => 'confirmed',
            'bukti_bayar' => $buktiBayar,
            'raw_wa_text' => $text,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking berhasil dibuat! Kode: ' . $kodeBooking);
    }

    public function confirm($id, FonnteService $fonnte): RedirectResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'confirmed']);

        $message = "✅ *Booking Terkonfirmasi!*\n\n"
            . "Kode Booking: *{$booking->kode_booking}*\n"
            . "Paket: {$booking->package->nama}\n"
            . "Tanggal: {$booking->tanggal}\n"
            . "Sesi: {$booking->sesi}\n"
            . "Jumlah: {$booking->jumlah_peserta} orang\n"
            . "Total: Rp " . number_format($booking->total_harga, 0, ',', '.') . "\n\n"
            . "Terima kasih, reservasi Anda telah dikonfirmasi. 🎉\n"
            . "Harap datang 15 menit sebelum jadwal.";

        $fonnte->sendMessage($booking->no_wa_pemesan, $message);

        return back()->with('success', 'Booking dikonfirmasi, notifikasi terkirim ke WA pelanggan');
    }

    public function destroy($id): RedirectResponse
    {
        Booking::findOrFail($id)->delete();
        return redirect()->route('admin.bookings.index')->with('success', 'Booking dihapus');
    }

    private function extractBookingData(string $text): array
    {
        $data = [
            'nama' => '',
            'no_wa' => '',
            'email' => null,
            'kota' => '',
            'catatan' => null,
            'package_name' => '',
            'tanggal' => '',
            'sesi' => '',
            'jumlah_peserta' => 1,
            'total_harga' => 0,
        ];

        // Normalize line endings and split
        foreach (explode("\n", $text) as $raw) {
            // Strip leading non-word characters (emojis, bullets, etc.) and normalize spaces
            $line = trim(preg_replace('/[^\p{L}\p{N}:.@\s\/-]+/u', ' ', $raw));
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (!$line) continue;

            // Paket
            if (preg_match('/^(?:Paket|Nama\s*Paket|Package|Pack)\s*:\s*(.+)$/iu', $line, $m))
                $data['package_name'] = trim($m[1]);

            // Tanggal
            elseif (preg_match('/^(?:Tanggal|Tgl|Date|Tangeal)\s*:\s*(.+)$/iu', $line, $m))
                $data['tanggal'] = trim($m[1]);

            // Sesi
            elseif (preg_match('/^(?:Sesi|Session|Jam|Waktu)\s*:\s*(.+)$/iu', $line, $m))
                $data['sesi'] = trim($m[1]);

            // Jumlah Peserta
            elseif (preg_match('/^(?:Peserta|Jumlah\s*Peserta|Pax|Orang|Peseta)\s*:\s*(\d+)/iu', $line, $m))
                $data['jumlah_peserta'] = (int) $m[1];

            // Total Harga
            elseif (preg_match('/^(?:Total|Total\s*Harga|Harga|Price|Biaya)\s*:\s*(?:Rp\.?\s*)?([\d.,]+)/iu', $line, $m))
                $data['total_harga'] = (int) str_replace(['.', ','], '', $m[1]);

            // Nama
            elseif (preg_match('/^(?:Nama|Name|Nama\s*Pemesan)\s*:\s*(.+)$/iu', $line, $m))
                $data['nama'] = trim($m[1]);

            // No WA
            elseif (preg_match('/^(?:WhatsApp|No\.?\s*WA|WA|Phone|Telepon|No\.?\s*HP|HP)\s*:\s*(.+)$/iu', $line, $m))
                $data['no_wa'] = trim($m[1]);

            // Email
            elseif (preg_match('/^(?:Email|E-mail|Mail|Surel)\s*:\s*(.+)$/iu', $line, $m))
                $data['email'] = trim($m[1]);

            // Kota
            elseif (preg_match('/^(?:Kota|City|Asal|Kota\s*Asal|Domisili)\s*:\s*(.+)$/iu', $line, $m))
                $data['kota'] = trim($m[1]);

            // Catatan
            elseif (preg_match('/^(?:Catatan|Note|Pesan|Keterangan)\s*:\s*(.+)$/iu', $line, $m))
                $data['catatan'] = trim($m[1]);
        }

        return $data;
    }

    public function export()
    {
        $bookings = Booking::with('package:id,nama')->orderBy('created_at', 'desc')->get();

        $path = tempnam(sys_get_temp_dir(), 'bookings') . '.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues([
            'Kode Booking', 'Nama Pemesan', 'No. WA', 'Email', 'Kota Asal',
            'Paket', 'Tanggal', 'Sesi', 'Jumlah Peserta', 'Total Harga',
            'Status', 'Catatan', 'Tanggal Booking',
        ]));

        foreach ($bookings as $b) {
            $writer->addRow(Row::fromValues([
                $b->kode_booking,
                $b->nama_pemesan,
                $b->no_wa_pemesan,
                $b->email ?? '',
                $b->kota_asal ?? '',
                $b->package?->nama ?? '',
                $b->tanggal,
                $b->sesi,
                $b->jumlah_peserta,
                $b->total_harga,
                $b->status,
                $b->catatan ?? '',
                $b->created_at->format('Y-m-d H:i'),
            ]));
        }

        $writer->close();

        return response()->download($path, 'bookings-export-' . now()->format('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }
}
