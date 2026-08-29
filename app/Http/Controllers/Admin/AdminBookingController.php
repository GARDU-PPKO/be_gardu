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
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
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

        $sheet = $writer->getCurrentSheet();

        // 1. Pembekuan Baris (Freeze Rows 1-4 sehingga Judul & Header Tabel Tetap di Atas)
        $sheet->setSheetView(new SheetView(freezeRow: 5, freezeColumn: 'A'));

        // 2. Atur Lebar Kolom yang Rapi dan Proporsional (Auto-fit Column Widths)
        $sheet->setColumnWidth(22, 1);  // A: Kode Booking
        $sheet->setColumnWidth(25, 2);  // B: Nama Pemesan
        $sheet->setColumnWidth(18, 3);  // C: No. WA
        $sheet->setColumnWidth(24, 4);  // D: Email
        $sheet->setColumnWidth(22, 5);  // E: Alamat
        $sheet->setColumnWidth(25, 6);  // F: Paket Wisata
        $sheet->setColumnWidth(20, 7);  // G: Tanggal Kunjungan
        $sheet->setColumnWidth(18, 8);  // H: Sesi
        $sheet->setColumnWidth(16, 9);  // I: Jumlah Peserta
        $sheet->setColumnWidth(22, 10); // J: Total Harga (Rp)
        $sheet->setColumnWidth(20, 11); // K: Status
        $sheet->setColumnWidth(30, 12); // L: Catatan
        $sheet->setColumnWidth(22, 13); // M: Tanggal Booking

        // 3. Style Definitions
        $titleStyle = new Style(
            fontBold: true,
            fontSize: 14,
            fontColor: '047857',
            fontName: 'Calibri',
        );

        $subtitleStyle = new Style(
            fontItalic: true,
            fontSize: 10,
            fontColor: '475569',
            fontName: 'Calibri',
        );

        $headerStyle = new Style(
            fontBold: true,
            fontSize: 11,
            fontColor: Color::WHITE,
            fontName: 'Calibri',
            cellAlignment: CellAlignment::CENTER,
            backgroundColor: '047857',
        );

        $totalRowStyle = new Style(
            fontBold: true,
            fontSize: 11,
            fontColor: '0F172A',
            fontName: 'Calibri',
            backgroundColor: 'E2E8F0',
        );

        $summaryHeaderStyle = new Style(
            fontBold: true,
            fontSize: 11,
            fontColor: Color::WHITE,
            fontName: 'Calibri',
            backgroundColor: '047857',
        );

        $summaryItemStyle = new Style(
            fontBold: true,
            fontSize: 10,
            fontColor: '1E293B',
            fontName: 'Calibri',
        );

        // 4. Baris Judul & Informasi Laporan (Header Banner Atas)
        $writer->addRow(Row::fromValuesWithStyle([
            'LAPORAN REKAPITULASI PEMESANAN WISATA - DESA GETAS',
        ], $titleStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Waktu Ekspor: ' . now()->format('d/m/Y H:i') . ' WIB  |  Total Data: ' . $bookings->count() . ' Transaksi',
        ], $subtitleStyle));

        $writer->addRow(Row::fromValues([])); // Baris Spasi Kosong

        // 5. Header Tabel Utama (Baris 4)
        $writer->addRow(Row::fromValuesWithStyle([
            'Kode Booking',
            'Nama Pemesan',
            'No. WhatsApp',
            'Email',
            'Alamat / Kota Asal',
            'Paket Wisata',
            'Tanggal Kunjungan',
            'Sesi Kunjungan',
            'Jumlah Peserta',
            'Total Harga (Rp)',
            'Status',
            'Catatan',
            'Tanggal Transaksi',
        ], $headerStyle));

        // 6. Data Rows & Akumulasi Statistik
        $totalPeserta = 0;
        $totalNilaiTransaksi = 0;
        $totalPendapatanConfirmed = 0;
        $countConfirmed = 0;
        $countPending = 0;
        $countCancelledOrRejected = 0;

        foreach ($bookings as $b) {
            $peserta = (int) $b->jumlah_peserta;
            $harga = (float) $b->total_harga;
            $totalPeserta += $peserta;
            $totalNilaiTransaksi += $harga;

            $status = strtolower($b->status ?? '');
            if ($status === 'confirmed' || $status === 'completed') {
                $countConfirmed++;
                $totalPendapatanConfirmed += $harga;
            } elseif (str_contains($status, 'pending')) {
                $countPending++;
            } elseif (str_contains($status, 'reject') || str_contains($status, 'cancel') || str_contains($status, 'expired')) {
                $countCancelledOrRejected++;
            }

            $statusLabel = strtoupper(str_replace('_', ' ', $b->status));
            $totalHargaFormatted = 'Rp ' . number_format($harga, 0, ',', '.');
            $tglKunjungan = $b->tanggal_kunjungan ? $b->tanggal_kunjungan->format('d/m/Y') : '-';
            $tglBooking = $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-';

            $writer->addRow(Row::fromValues([
                $b->booking_code,
                $b->nama_lengkap,
                $b->no_whatsapp,
                $b->email ?: '-',
                $b->alamat ?: '-',
                $b->paketWisata?->nama ?: '-',
                $tglKunjungan,
                $b->sesi ?: '-',
                $peserta . ' Orang',
                $totalHargaFormatted,
                $statusLabel,
                $b->notes ?: '-',
                $tglBooking,
            ]));
        }

        // 7. Baris Total Tabel
        $writer->addRow(Row::fromValuesWithStyle([
            'TOTAL KESELURUHAN',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalPeserta . ' Orang',
            'Rp ' . number_format($totalNilaiTransaksi, 0, ',', '.'),
            '',
            '',
            '',
        ], $totalRowStyle));

        // 8. Bagian Kesimpulan & Ringkasan Laporan
        $writer->addRow(Row::fromValues([])); // Baris Pemisah
        $writer->addRow(Row::fromValues([])); // Baris Pemisah

        $writer->addRow(Row::fromValuesWithStyle([
            'RINGKASAN & KESIMPULAN LAPORAN',
            '',
        ], $summaryHeaderStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Total Seluruh Pemesanan',
            $bookings->count() . ' Transaksi',
        ], $summaryItemStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Total Pengunjung (Peserta)',
            $totalPeserta . ' Orang',
        ], $summaryItemStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Pendapatan Terkonfirmasi (Confirmed)',
            'Rp ' . number_format($totalPendapatanConfirmed, 0, ',', '.'),
        ], $summaryItemStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Estimasi Nilai Seluruh Transaksi',
            'Rp ' . number_format($totalNilaiTransaksi, 0, ',', '.'),
        ], $summaryItemStyle));

        $writer->addRow(Row::fromValuesWithStyle([
            'Rincian Status Pemesanan',
            "Terkonfirmasi: {$countConfirmed}  |  Menunggu (Pending): {$countPending}  |  Batal/Ditolak: {$countCancelledOrRejected}",
        ], $summaryItemStyle));

        $writer->close();

        return response()->download($path, 'bookings-export-' . now()->format('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }
}
