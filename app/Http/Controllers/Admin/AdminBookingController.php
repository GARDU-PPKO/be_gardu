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

        $confirmedBookings = $bookings->filter(function ($b) {
            $status = strtoupper((string) ($b->status ?? ''));
            return in_array($status, [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED, 'CONFIRMED', 'COMPLETED']);
        });

        $dataCount = $bookings->count();
        $confirmedCount = $confirmedBookings->count();

        // Perhitungan indeks baris Excel untuk mergeCells yang presisi
        $totalRow1Index = 5 + $dataCount + 1;
        $table2BannerIndex = $totalRow1Index + 3;
        $table2HeaderIndex = $table2BannerIndex + 1;
        $totalRow2Index = $table2HeaderIndex + $confirmedCount + 1;
        $summaryTitleIndex = $totalRow2Index + 3;

        // 1. Inisialisasi Options (Merge Cells jika didukung)
        $options = null;
        if (class_exists(Options::class)) {
            try {
                $options = new Options();
                if (method_exists($options, 'mergeCells')) {
                    $options->mergeCells(0, 1, 6, 1);
                    $options->mergeCells(0, 2, 6, 2);
                    $options->mergeCells(0, 4, 6, 4);
                    $options->mergeCells(0, $totalRow1Index, 8, $totalRow1Index);
                    $options->mergeCells(0, $table2BannerIndex, 6, $table2BannerIndex);
                    $options->mergeCells(0, $totalRow2Index, 8, $totalRow2Index);
                    $options->mergeCells(0, $summaryTitleIndex, 2, $summaryTitleIndex);
                }
            } catch (\Throwable) {
                $options = null;
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'bookings') . '.xlsx';
        
        // 2. Inisialisasi Writer yang kompatibel di semua versi
        if ($options !== null) {
            try {
                $writer = new Writer($options);
            } catch (\Throwable) {
                $writer = new Writer();
            }
        } else {
            $writer = new Writer();
        }

        $writer->openToFile($path);

        // 3. Atur Lebar Kolom jika didukung
        try {
            $sheet = method_exists($writer, 'getCurrentSheet') ? $writer->getCurrentSheet() : null;
            if ($sheet && method_exists($sheet, 'setColumnWidth')) {
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
            }
        } catch (\Throwable) {}

        // 4. Definisi Style Universal (100% aman untuk semua versi library)
        $titleStyle = $this->makeExportStyle(bold: true, fontSize: 14, fontColor: '047857', fontName: 'Calibri');
        $subtitleStyle = $this->makeExportStyle(italic: true, fontSize: 10, fontColor: '475569', fontName: 'Calibri');
        $sectionBannerStyle = $this->makeExportStyle(bold: true, fontSize: 11, fontColor: 'FFFFFF', fontName: 'Calibri', backgroundColor: '065F46');
        $headerStyle = $this->makeExportStyle(bold: true, fontSize: 11, fontColor: 'FFFFFF', fontName: 'Calibri', alignment: CellAlignment::CENTER, backgroundColor: '047857');
        $totalRowStyle = $this->makeExportStyle(bold: true, fontSize: 11, fontColor: '0F172A', fontName: 'Calibri', backgroundColor: 'E2E8F0');
        $summaryTitleStyle = $this->makeExportStyle(bold: true, fontSize: 11, fontColor: 'FFFFFF', fontName: 'Calibri', alignment: CellAlignment::CENTER, backgroundColor: '047857');
        $summaryHeaderSubStyle = $this->makeExportStyle(bold: true, fontSize: 10, fontColor: '0F172A', fontName: 'Calibri', alignment: CellAlignment::CENTER, backgroundColor: 'D1FAE5');
        $summaryItemStyle = $this->makeExportStyle(fontSize: 10, fontColor: '1E293B', fontName: 'Calibri');
        $summaryItemBoldStyle = $this->makeExportStyle(bold: true, fontSize: 10, fontColor: '0F172A', fontName: 'Calibri');

        // Helper format data satu baris booking
        $formatBookingRow = function ($b) {
            $peserta = (int) $b->jumlah_peserta;
            $harga = (float) $b->total_harga;

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

            $addonsText = $b->addOns->isNotEmpty()
                ? $b->addOns->map(fn ($a) => $a->nama . ($a->pivot?->qty > 1 ? " ({$a->pivot->qty}x)" : ''))->join(', ')
                : '-';

            return [
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
            ];
        };

        // 5. Header Banner Laporan (Atas)
        $writer->addRow($this->makeExportRow(['LAPORAN REKAPITULASI PEMESANAN WISATA - DESA GETAS'], $titleStyle));
        $writer->addRow($this->makeExportRow(['Waktu Ekspor: ' . now()->format('d/m/Y H:i') . ' WIB  |  Total Data: ' . $dataCount . ' Transaksi', '', '', '', '', '', ''], $subtitleStyle));
        $writer->addRow($this->makeExportRow([]));

        // ── TABEL 1: DAFTAR SELURUH PEMESANAN (ALL DATA) ──
        $writer->addRow($this->makeExportRow(['1. DAFTAR SELURUH PEMESANAN (SEMUA STATUS)', '', '', '', '', '', ''], $sectionBannerStyle));
        $writer->addRow($this->makeExportRow([
            'Kode Booking', 'Nama Pemesan', 'No. WhatsApp', 'Email', 'Alamat / Kota Asal', 'Paket Wisata', 'Add-ons / Layanan Tambahan', 'Tanggal Kunjungan', 'Sesi Kunjungan', 'Jumlah Peserta', 'Total Harga (Rp)', 'Status', 'Catatan', 'Tanggal Transaksi',
        ], $headerStyle));

        $totalPesertaAll = 0;
        $totalNilaiAll = 0;
        $totalPendapatanConfirmed = 0;
        $countConfirmed = 0;
        $countPending = 0;
        $countCancelledOrRejected = 0;

        foreach ($bookings as $b) {
            $peserta = (int) $b->jumlah_peserta;
            $harga = (float) $b->total_harga;
            $totalPesertaAll += $peserta;
            $totalNilaiAll += $harga;

            $status = strtoupper((string) ($b->status ?? ''));
            if (in_array($status, [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED, 'CONFIRMED', 'COMPLETED'])) {
                $countConfirmed++;
                $totalPendapatanConfirmed += $harga;
            } elseif (str_contains($status, 'PENDING')) {
                $countPending++;
            } elseif (in_array($status, [Booking::STATUS_REJECTED, Booking::STATUS_CANCELLED, Booking::STATUS_EXPIRED, 'REJECTED', 'CANCELLED', 'EXPIRED']) || str_contains($status, 'REJECT') || str_contains($status, 'CANCEL')) {
                $countCancelledOrRejected++;
            }

            $writer->addRow($this->makeExportRow($formatBookingRow($b)));
        }

        // Total Tabel 1
        $writer->addRow($this->makeExportRow([
            'TOTAL KESELURUHAN (ALL DATA)', '', '', '', '', '', '', '', '',
            $totalPesertaAll . ' Orang',
            'Rp ' . number_format($totalNilaiAll, 0, ',', '.'),
            '', '', '',
        ], $totalRowStyle));

        // ── TABEL 2: DAFTAR PEMESANAN TERKONFIRMASI (CONFIRMED) ──
        $writer->addRow($this->makeExportRow([]));
        $writer->addRow($this->makeExportRow([]));

        $writer->addRow($this->makeExportRow(['2. DAFTAR PEMESANAN TERKONFIRMASI (CONFIRMED / SIAP DILAYANI)', '', '', '', '', '', ''], $sectionBannerStyle));
        $writer->addRow($this->makeExportRow([
            'Kode Booking', 'Nama Pemesan', 'No. WhatsApp', 'Email', 'Alamat / Kota Asal', 'Paket Wisata', 'Add-ons / Layanan Tambahan', 'Tanggal Kunjungan', 'Sesi Kunjungan', 'Jumlah Peserta', 'Total Harga (Rp)', 'Status', 'Catatan', 'Tanggal Transaksi',
        ], $headerStyle));

        $totalPesertaConfirmed = 0;
        if ($confirmedBookings->isEmpty()) {
            $writer->addRow($this->makeExportRow([
                '-', 'Belum ada pemesanan terkonfirmasi', '-', '-', '-', '-', '-', '-', '-', '0 Orang', 'Rp 0', '-', '-', '-',
            ]));
        } else {
            foreach ($confirmedBookings as $b) {
                $totalPesertaConfirmed += (int) $b->jumlah_peserta;
                $writer->addRow($this->makeExportRow($formatBookingRow($b)));
            }
        }

        // Total Tabel 2
        $writer->addRow($this->makeExportRow([
            'TOTAL PEMESANAN TERKONFIRMASI', '', '', '', '', '', '', '', '',
            $totalPesertaConfirmed . ' Orang',
            'Rp ' . number_format($totalPendapatanConfirmed, 0, ',', '.'),
            '', '', '',
        ], $totalRowStyle));

        // ── TABEL 3: REKAPITULASI & KESIMPULAN (REKAP) ──
        $writer->addRow($this->makeExportRow([]));
        $writer->addRow($this->makeExportRow([]));

        $writer->addRow($this->makeExportRow(['3. RINGKASAN & KESIMPULAN LAPORAN', '', ''], $summaryTitleStyle));
        $writer->addRow($this->makeExportRow(['INDIKATOR / PARAMETER', 'JUMLAH / NILAI', 'KETERANGAN STATUS'], $summaryHeaderSubStyle));
        $writer->addRow($this->makeExportRow(['Total Seluruh Pemesanan', $dataCount . ' Transaksi', 'Semua data pemesanan yang tercatat'], $summaryItemStyle));
        $writer->addRow($this->makeExportRow(['Total Pengunjung (Peserta)', $totalPesertaAll . ' Orang', 'Akumulasi seluruh peserta wisata'], $summaryItemStyle));
        $writer->addRow($this->makeExportRow(['Pendapatan Terkonfirmasi', 'Rp ' . number_format($totalPendapatanConfirmed, 0, ',', '.'), 'Pemesanan status Confirmed / Lunas'], $summaryItemBoldStyle));
        $writer->addRow($this->makeExportRow(['Estimasi Nilai Seluruh Transaksi', 'Rp ' . number_format($totalNilaiAll, 0, ',', '.'), 'Total nilai pesanan (semua status)'], $summaryItemStyle));
        $writer->addRow($this->makeExportRow(['Pemesanan Terkonfirmasi', $countConfirmed . ' Booking', 'Pembayaran valid & siap berkunjung'], $summaryItemStyle));
        $writer->addRow($this->makeExportRow(['Pemesanan Menunggu (Pending)', $countPending . ' Booking', 'Menunggu bukti / verifikasi admin'], $summaryItemStyle));
        $writer->addRow($this->makeExportRow(['Pemesanan Batal / Ditolak', $countCancelledOrRejected . ' Booking', 'Dibatalkan pemesan atau ditolak admin'], $summaryItemStyle));

        $writer->close();

        return response()->download($path, 'bookings-export-' . now()->format('Y-m-d') . '.xlsx')->deleteFileAfterSend(true);
    }

    private function makeExportRow(array $values, mixed $style = null): Row
    {
        if ($style !== null && method_exists(Row::class, 'fromValuesWithStyle')) {
            try {
                return Row::fromValuesWithStyle($values, $style);
            } catch (\Throwable) {}
        }

        if (class_exists('\OpenSpout\Common\Creator\WriterEntityFactory')) {
            try {
                if ($style !== null) {
                    return \OpenSpout\Common\Creator\WriterEntityFactory::createRowFromArray($values, $style);
                }
                return \OpenSpout\Common\Creator\WriterEntityFactory::createRowFromArray($values);
            } catch (\Throwable) {}
        }

        try {
            $row = Row::fromValues($values);
            if ($style !== null) {
                if (method_exists($row, 'withStyle')) {
                    $row = $row->withStyle($style);
                } elseif (method_exists($row, 'setStyle')) {
                    $row->setStyle($style);
                }
            }
            return $row;
        } catch (\Throwable) {}

        return Row::fromValues($values);
    }

    private function makeExportStyle(
        bool $bold = false,
        bool $italic = false,
        int $fontSize = 11,
        mixed $fontColor = '000000',
        string $fontName = 'Calibri',
        mixed $alignment = null,
        ?string $backgroundColor = null
    ): object {
        $fontColorStr = is_object($fontColor) && property_exists($fontColor, 'value') ? (string) $fontColor->value : (string) $fontColor;

        if (class_exists('\OpenSpout\Common\Entity\Style\StyleBuilder')) {
            try {
                $b = new \OpenSpout\Common\Entity\Style\StyleBuilder();
                if ($bold && method_exists($b, 'setFontBold')) $b->setFontBold();
                if ($italic && method_exists($b, 'setFontItalic')) $b->setFontItalic();
                if (method_exists($b, 'setFontSize')) $b->setFontSize($fontSize);
                if (method_exists($b, 'setFontColor')) $b->setFontColor($fontColorStr);
                if (method_exists($b, 'setFontName')) $b->setFontName($fontName);
                if ($alignment && method_exists($b, 'setCellAlignment')) {
                    $alignVal = is_object($alignment) && property_exists($alignment, 'value') ? (string) $alignment->value : (string) $alignment;
                    $b->setCellAlignment($alignVal);
                }
                if ($backgroundColor && method_exists($b, 'setBackgroundColor')) $b->setBackgroundColor($backgroundColor);
                return $b->build();
            } catch (\Throwable) {}
        }

        $style = new Style();

        if (method_exists($style, 'withFontBold')) {
            try {
                if ($bold) $style = $style->withFontBold(true);
                if ($italic) $style = $style->withFontItalic(true);
                if ($fontSize !== 11) $style = $style->withFontSize($fontSize);
                if ($fontColorStr !== '000000') $style = $style->withFontColor($fontColorStr);
                if ($fontName !== 'Calibri') $style = $style->withFontName($fontName);
                if ($alignment) {
                    if ($alignment instanceof CellAlignment) {
                        $style = $style->withCellAlignment($alignment);
                    } elseif (enum_exists(CellAlignment::class) && is_string($alignment)) {
                        $enumVal = CellAlignment::tryFrom($alignment) ?? CellAlignment::CENTER;
                        $style = $style->withCellAlignment($enumVal);
                    }
                }
                if ($backgroundColor) $style = $style->withBackgroundColor($backgroundColor);
                return $style;
            } catch (\Throwable) {}
        }

        if (method_exists($style, 'setFontBold')) {
            try {
                if ($bold) $style->setFontBold(true);
                if ($italic) $style->setFontItalic(true);
                $style->setFontSize($fontSize);
                $style->setFontColor($fontColorStr);
                $style->setFontName($fontName);
                if ($alignment && method_exists($style, 'setCellAlignment')) {
                    $alignVal = is_object($alignment) && property_exists($alignment, 'value') ? (string) $alignment->value : (string) $alignment;
                    $style->setCellAlignment($alignVal);
                }
                if ($backgroundColor && method_exists($style, 'setBackgroundColor')) $style->setBackgroundColor($backgroundColor);
                return $style;
            } catch (\Throwable) {}
        }

        try {
            $ref = new \ReflectionClass(Style::class);
            $ctor = $ref->getConstructor();
            if ($ctor && count($ctor->getParameters()) > 0) {
                $args = [];
                foreach ($ctor->getParameters() as $param) {
                    $name = $param->getName();
                    if ($name === 'fontBold') $args[] = $bold;
                    elseif ($name === 'fontItalic') $args[] = $italic;
                    elseif ($name === 'fontSize') $args[] = $fontSize;
                    elseif ($name === 'fontColor') $args[] = $fontColorStr;
                    elseif ($name === 'fontName') $args[] = $fontName;
                    elseif ($name === 'cellAlignment') $args[] = $alignment;
                    elseif ($name === 'backgroundColor') $args[] = $backgroundColor;
                    else $args[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                }
                return $ref->newInstanceArgs($args);
            }
        } catch (\Throwable) {}

        return new Style();
    }
}
