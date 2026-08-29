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
use OpenSpout\Writer\XLSX\Options;
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

    public function export()
    {
        $bookings = Booking::with(['paketWisata:id,nama', 'addOns'])->orderBy('created_at', 'desc')->get();

        $dataCount = $bookings->count();
        $totalRowIndex = 5 + $dataCount;
        $summaryTitleRowIndex = $totalRowIndex + 3;

        $options = new Options();
        // Merge judul banner & total & header ringkasan
        $options->mergeCells(0, 1, 6, 1);
        $options->mergeCells(0, 2, 6, 2);
        $options->mergeCells(0, $totalRowIndex, 8, $totalRowIndex);
        $options->mergeCells(0, $summaryTitleRowIndex, 2, $summaryTitleRowIndex);

        $path = tempnam(sys_get_temp_dir(), 'bookings') . '.xlsx';
        $writer = new Writer($options);
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();

        // 1. Pembekuan Baris (Freeze Rows 1-4 sehingga Judul & Header Tabel Tetap di Atas)
        $sheet->setSheetView(new SheetView(freezeRow: 5, freezeColumn: 'A'));

        // 2. Atur Lebar Kolom yang Rapi dan Proporsional
        $sheet->setColumnWidth(30, 1);  // A: Kode Booking / Indikator Ringkasan
        $sheet->setColumnWidth(26, 2);  // B: Nama Pemesan / Nilai Ringkasan
        $sheet->setColumnWidth(38, 3);  // C: No. WA / Keterangan Status Ringkasan
        $sheet->setColumnWidth(26, 4);  // D: Email
        $sheet->setColumnWidth(22, 5);  // E: Alamat
        $sheet->setColumnWidth(26, 6);  // F: Paket Wisata
        $sheet->setColumnWidth(30, 7);  // G: Add-ons / Layanan Tambahan
        $sheet->setColumnWidth(20, 8);  // H: Tanggal Kunjungan
        $sheet->setColumnWidth(18, 9);  // I: Sesi
        $sheet->setColumnWidth(16, 10); // J: Jumlah Peserta
        $sheet->setColumnWidth(22, 11); // K: Total Harga (Rp)
        $sheet->setColumnWidth(20, 12); // L: Status
        $sheet->setColumnWidth(30, 13); // M: Catatan
        $sheet->setColumnWidth(22, 14); // N: Tanggal Transaksi

        // 3. Style Definitions
        $titleStyle = new Style(fontBold: true, fontSize: 14, fontColor: '047857', fontName: 'Calibri');
        $subtitleStyle = new Style(fontItalic: true, fontSize: 10, fontColor: '475569', fontName: 'Calibri');
        $headerStyle = new Style(fontBold: true, fontSize: 11, fontColor: Color::WHITE, fontName: 'Calibri', cellAlignment: CellAlignment::CENTER, backgroundColor: '047857');
        $totalRowStyle = new Style(fontBold: true, fontSize: 11, fontColor: '0F172A', fontName: 'Calibri', backgroundColor: 'E2E8F0');
        $summaryTitleStyle = new Style(fontBold: true, fontSize: 11, fontColor: Color::WHITE, fontName: 'Calibri', cellAlignment: CellAlignment::CENTER, backgroundColor: '047857');
        $summaryHeaderSubStyle = new Style(fontBold: true, fontSize: 10, fontColor: '0F172A', fontName: 'Calibri', cellAlignment: CellAlignment::CENTER, backgroundColor: 'D1FAE5');
        $summaryItemStyle = new Style(fontSize: 10, fontColor: '1E293B', fontName: 'Calibri');
        $summaryItemBoldStyle = new Style(fontBold: true, fontSize: 10, fontColor: '0F172A', fontName: 'Calibri');

        // 4. Baris Judul & Informasi Laporan (Header Banner Atas)
        $writer->addRow(Row::fromValuesWithStyle(['LAPORAN REKAPITULASI PEMESANAN WISATA - DESA GETAS'], $titleStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Waktu Ekspor: ' . now()->format('d/m/Y H:i') . ' WIB  |  Total Data: ' . $dataCount . ' Transaksi'], $subtitleStyle));
        $writer->addRow(Row::fromValues([]));

        // 5. Header Tabel Utama (Baris 4)
        $writer->addRow(Row::fromValuesWithStyle([
            'Kode Booking',
            'Nama Pemesan',
            'No. WhatsApp',
            'Email',
            'Alamat / Kota Asal',
            'Paket Wisata',
            'Add-ons / Layanan Tambahan',
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
            if (in_array($status, [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])) {
                $countConfirmed++;
                $totalPendapatanConfirmed += $harga;
            } elseif (str_contains($status, 'pending')) {
                $countPending++;
            } elseif (str_contains($status, 'reject') || str_contains($status, 'cancel') || str_contains($status, 'expired')) {
                $countCancelledOrRejected++;
            }

            // Format No. WA agar Excel mengenalinya sebagai nomor kontak internasional (hilang tanda segitiga hijau)
            $rawWa = preg_replace('/[^\d]/', '', (string) $b->no_whatsapp);
            if (str_starts_with($rawWa, '0')) {
                $formattedWa = '+62 ' . substr($rawWa, 1);
            } elseif (str_starts_with($rawWa, '62')) {
                $formattedWa = '+62 ' . substr($rawWa, 2);
            } elseif ($rawWa) {
                $formattedWa = '+' . $rawWa;
            } else {
                $formattedWa = '-';
            }

            // Format Add-ons
            $addonsText = $b->addOns->isNotEmpty()
                ? $b->addOns->map(fn ($a) => $a->nama . ($a->pivot?->qty > 1 ? " ({$a->pivot->qty}x)" : ''))->join(', ')
                : '-';

            $writer->addRow(Row::fromValues([
                $b->booking_code,
                $b->nama_lengkap,
                $formattedWa,
                $b->email ?: '-',
                $b->alamat ?: '-',
                $b->paketWisata?->nama ?: '-',
                $addonsText,
                $b->tanggal_kunjungan?->format('d/m/Y') ?: '-',
                $b->sesi ?: '-',
                $peserta . ' Orang',
                'Rp ' . number_format($harga, 0, ',', '.'),
                strtoupper(str_replace('_', ' ', $b->status)),
                $b->notes ?: '-',
                $b->created_at?->format('d/m/Y H:i') ?: '-',
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
            '',
            $totalPeserta . ' Orang',
            'Rp ' . number_format($totalNilaiTransaksi, 0, ',', '.'),
            '',
            '',
            '',
        ], $totalRowStyle));

        // 8. Bagian Tabel Kesimpulan & Ringkasan Laporan
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValuesWithStyle(['RINGKASAN & KESIMPULAN LAPORAN', '', ''], $summaryTitleStyle));
        $writer->addRow(Row::fromValuesWithStyle(['INDIKATOR / PARAMETER', 'JUMLAH / NILAI', 'KETERANGAN STATUS'], $summaryHeaderSubStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Total Seluruh Pemesanan', $dataCount . ' Transaksi', 'Seluruh data pemesanan yang masuk'], $summaryItemStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Total Pengunjung (Peserta)', $totalPeserta . ' Orang', 'Akumulasi seluruh peserta wisata'], $summaryItemStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Pendapatan Terkonfirmasi', 'Rp ' . number_format($totalPendapatanConfirmed, 0, ',', '.'), 'Pemesanan status Confirmed / Lunas'], $summaryItemBoldStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Estimasi Nilai Seluruh Transaksi', 'Rp ' . number_format($totalNilaiTransaksi, 0, ',', '.'), 'Total nilai pesanan (semua status)'], $summaryItemStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Pemesanan Terkonfirmasi', $countConfirmed . ' Booking', 'Pembayaran valid & siap berkunjung'], $summaryItemStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Pemesanan Menunggu (Pending)', $countPending . ' Booking', 'Menunggu bukti / verifikasi admin'], $summaryItemStyle));
        $writer->addRow(Row::fromValuesWithStyle(['Pemesanan Batal / Ditolak', $countCancelledOrRejected . ' Booking', 'Dibatalkan pemesan atau ditolak admin'], $summaryItemStyle));

        $writer->close();

        return response()->download($path, 'bookings-export-' . now()->format('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }
}
