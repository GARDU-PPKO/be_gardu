<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
            'bukti_bayar' => 'nullable|string|max:255',
        ]);

        $text = $request->raw_text;
        $data = $this->extractBookingData($text);

        $package = TourPackage::where('nama', 'like', '%' . $data['package_name'] . '%')->first();

        if (!$package) {
            return back()->with('parse_error', 'Paket "' . $data['package_name'] . '" tidak ditemukan. Periksa teks dan coba lagi.')
                ->withInput()->with('parsed_data', $data);
        }

        $kodeBooking = 'GB-' . strtoupper(Str::random(8));

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
            'status' => 'pending',
            'bukti_bayar' => $request->bukti_bayar,
            'raw_wa_text' => $text,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Booking berhasil dibuat! Kode: ' . $kodeBooking);
    }

    public function confirm($id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'confirmed']);
        return back()->with('success', 'Booking dikonfirmasi');
    }

    public function cancel($id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);
        return back()->with('success', 'Booking dibatalkan');
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

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if (preg_match('/📦\s*Paket:\s*(.+)/i', $line, $m))
                $data['package_name'] = trim($m[1]);
            elseif (preg_match('/📅\s*Tanggal:\s*(.+)/i', $line, $m))
                $data['tanggal'] = trim($m[1]);
            elseif (preg_match('/⏰\s*Sesi:\s*(.+)/i', $line, $m))
                $data['sesi'] = trim($m[1]);
            elseif (preg_match('/👥\s*Peserta:\s*(\d+)/i', $line, $m))
                $data['jumlah_peserta'] = (int) $m[1];
            elseif (preg_match('/💰\s*Total:\s*(?:Rp|Rp\.)?\s*([\d.,]+)/i', $line, $m))
                $data['total_harga'] = (int) str_replace(['.', ','], '', $m[1]);
            elseif (preg_match('/👤\s*Nama:\s*(.+)/i', $line, $m))
                $data['nama'] = trim($m[1]);
            elseif (preg_match('/📱\s*WhatsApp:\s*(.+)/i', $line, $m))
                $data['no_wa'] = trim($m[1]);
            elseif (preg_match('/✉️\s*Email:\s*(.+)/i', $line, $m))
                $data['email'] = trim($m[1]);
            elseif (preg_match('/🏙️\s*Kota:\s*(.+)/i', $line, $m))
                $data['kota'] = trim($m[1]);
            elseif (preg_match('/📝\s*Catatan:\s*(.+)/i', $line, $m))
                $data['catatan'] = trim($m[1]);
        }

        return $data;
    }
}
