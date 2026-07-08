<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBookingController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status'         => ['nullable', 'string', 'in:pending,confirmed,cancelled'],
            'tanggal_dari'   => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);

        $query = Booking::with('package:id,nama')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        return view('admin.bookings.index', [
            'bookings' => $query->paginate(15),
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

    public function exportExcel(Request $request)
    {
        $request->validate([
            'status'         => ['nullable', 'string', 'in:pending,confirmed,cancelled'],
            'tanggal_dari'   => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);

        $query = Booking::with('package:id,nama')->orderBy('tanggal', 'desc');
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('tanggal_dari'))   $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        if ($request->filled('tanggal_sampai')) $query->whereDate('tanggal', '<=', $request->tanggal_sampai);

        $bookings = $query->get();

        $parts = [];
        if ($request->filled('status'))         $parts[] = 'Status: ' . ucfirst($request->status);
        if ($request->filled('tanggal_dari'))   $parts[] = 'Dari: ' . \Carbon\Carbon::parse($request->tanggal_dari)->format('d/m/Y');
        if ($request->filled('tanggal_sampai')) $parts[] = 'Sampai: ' . \Carbon\Carbon::parse($request->tanggal_sampai)->format('d/m/Y');
        $filterLabel = $parts ? implode(' | ', $parts) : 'Semua Data';

        [$writer, $styles, $tmpFile] = $this->buildBookingWriter();

        $writer->addRow($this->row(['LAPORAN DATA BOOKING — DESA GETAS'], $styles['title'], 13));
        $writer->addRow($this->row(['Kec. Singorojo, Kab. Kendal'], $styles['sub'], 13));
        $writer->addRow($this->row(['']));
        $writer->addRow($this->row(['']));
        $writer->addRow($this->row(['Tanggal Cetak: ' . now()->format('d/m/Y H:i') . ' WIB', '', '', '', 'Filter: ' . $filterLabel]));        

        $headerRow = 6;
        $writer->addRow($this->row([
            'No.', 'Kode Booking', 'Nama Pemesan', 'No. WhatsApp',
            'Paket Wisata', 'Tanggal', 'Sesi', 'Peserta', 'Total Harga (Rp)',
            'Kota Asal', 'Catatan', 'Status', 'Dibuat Pada',
        ], $styles['head']));

        foreach ($bookings as $i => $b) {
            $writer->addRow($this->row([
                $i + 1,
                $b->kode_booking,
                $b->nama_pemesan,
                $b->no_wa_pemesan,
                $b->package->nama ?? '-',
                $b->tanggal?->format('d/m/Y') ?? '',
                $b->sesi,
                $b->jumlah_peserta,
                $b->total_harga,
                $b->kota_asal ?? '',
                $b->catatan ?? '',
                ucfirst($b->status),
                $b->created_at->format('d/m/Y H:i'),
            ], $styles['data']));
        }

        $firstDataRow = $headerRow + 1;
        $lastDataRow  = $headerRow + max($bookings->count(), 1);
        $statusRange  = "L{$firstDataRow}:L{$lastDataRow}";
        $totalRange   = "I{$firstDataRow}:I{$lastDataRow}";
        $kodeRange    = "B{$firstDataRow}:B{$lastDataRow}";

        $writer->addRow($this->row(['']));

        $this->summaryRow($writer, $styles, 'Total Booking', "=COUNTA({$kodeRange})&\" transaksi\"");
        $this->summaryRow($writer, $styles, 'Booking Confirmed', "=COUNTIF({$statusRange},\"Confirmed\")&\" transaksi\"");
        $this->summaryRow($writer, $styles, 'Booking Pending', "=COUNTIF({$statusRange},\"Pending\")&\" transaksi\"");
        $this->summaryRow($writer, $styles, 'Booking Cancelled', "=COUNTIF({$statusRange},\"Cancelled\")&\" transaksi\"");
        $this->summaryRow($writer, $styles, 'Total Pendapatan (Confirmed)', "=SUMIF({$statusRange},\"Confirmed\",{$totalRange})");

        $writer->addRow($this->row(['']));
        $writer->addRow($this->row(
            ['* Laporan dicetak otomatis dari Sistem Admin Desa Getas — ' . now()->format('d/m/Y H:i') . ' WIB'],
            $styles['grey']
        ));

        $writer->close();

        $filename = 'Laporan_Booking_' . now()->format('d-m-Y') . '.xlsx';

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildBookingWriter(): array
    {
        $b = fn($name) => "\\OpenSpout\\Common\\Entity\\Style\\{$name}";
        $Border      = $b('Border');
        $BorderPart  = $b('BorderPart');
        $BorderName  = $b('BorderName');
        $BorderWidth = $b('BorderWidth');
        $BorderStyle = $b('BorderStyle');
        $Style       = $b('Style');
        $Align       = $b('CellAlignment');

        $side = fn($name) => new $BorderPart($name, '000000', $BorderWidth::THIN, $BorderStyle::SOLID);
        $border = new $Border(
            $side($BorderName::BOTTOM), $side($BorderName::TOP),
            $side($BorderName::LEFT), $side($BorderName::RIGHT)
        );

        $styles = [
            'title'  => (new $Style())->withFontBold(true)->withCellAlignment($Align::CENTER),
            'sub'    => (new $Style())->withCellAlignment($Align::CENTER),
            'head'   => (new $Style())->withFontBold(true)->withFontColor('FFFFFF')
                            ->withBackgroundColor('374151')->withShouldWrapText(false)->withBorder($border),
            'data'   => (new $Style())->withBorder($border),
            'sumLbl' => (new $Style())->withFontBold(true)->withBackgroundColor('FFFF00')->withBorder($border),
            'sumVal' => (new $Style())->withFontBold(true)->withBackgroundColor('FFFF00')->withBorder($border)->withFormat('#,##0'),
            'grey'   => (new $Style())->withFontColor('6B7280'),
            'empty'  => (new $Style()),
        ];

        $options = new \OpenSpout\Writer\XLSX\Options();
        $options->setColumnWidth(6,  1);  
        $options->setColumnWidth(18, 2);  
        $options->setColumnWidth(25, 3);  
        $options->setColumnWidth(16, 4);  
        $options->setColumnWidth(22, 5); 
        $options->setColumnWidth(12, 6);
        $options->setColumnWidth(10, 7);
        $options->setColumnWidth(9,  8);
        $options->setColumnWidth(16, 9);
        $options->setColumnWidth(15, 10);
        $options->setColumnWidth(25, 11);
        $options->setColumnWidth(12, 12);
        $options->setColumnWidth(18, 13);

        $options->mergeCells(0, 1, 12, 1);
        $options->mergeCells(0, 2, 12, 2);
        $options->mergeCells(0, 4, 2, 4);
        $options->mergeCells(4, 4, 12, 4);

        $tmpFile = tempnam(sys_get_temp_dir(), 'booking_') . '.xlsx';
        $writer  = new \OpenSpout\Writer\XLSX\Writer($options);
        $writer->openToFile($tmpFile);

        return [$writer, $styles, $tmpFile];
    }

    private function row(array $vals, ?\OpenSpout\Common\Entity\Style\Style $style = null, ?int $padTo = null): \OpenSpout\Common\Entity\Row
    {
        if ($padTo && count($vals) < $padTo) {
            $vals = array_merge($vals, array_fill(0, $padTo - count($vals), ''));
        }

        return $style
            ? \OpenSpout\Common\Entity\Row::fromValuesWithStyle($vals, $style)
            : \OpenSpout\Common\Entity\Row::fromValues($vals);
    }

    private function summaryRow(\OpenSpout\Writer\XLSX\Writer $writer, array $styles, string $label, string $formula): void
    {
        $cells = [];
        for ($i = 0; $i < 10; $i++) {
            $cells[] = \OpenSpout\Common\Entity\Cell::fromValue('')->withStyle($styles['empty']);
        }

        $cells[] = \OpenSpout\Common\Entity\Cell::fromValue($label)->withStyle($styles['sumLbl']);
        $cells[] = new \OpenSpout\Common\Entity\Cell\FormulaCell($formula, null, $styles['sumVal']);
        $cells[] = \OpenSpout\Common\Entity\Cell::fromValue('')->withStyle($styles['empty']);

        $writer->addRow(new \OpenSpout\Common\Entity\Row($cells));
    }
}
