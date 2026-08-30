@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Bookings</h2>
            <p class="text-xs text-gray-500 mt-1">Default filter: menunggu verifikasi (FIFO)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openExportModal()" class="px-4 py-2 bg-white border border-emerald-700 text-emerald-700 rounded-lg text-sm hover:bg-emerald-50 transition flex items-center gap-1.5 shadow-xs font-medium cursor-pointer">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
            <a href="{{ route('admin.bookings.parse') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Parse Text WA</a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-2">
        @foreach([
            'PENDING_VERIFY' => 'Menunggu Verifikasi',
            'PENDING_PAYMENT' => 'Menunggu Pembayaran',
            'CONFIRMED' => 'Dikonfirmasi',
            'REJECTED' => 'Ditolak',
            'EXPIRED' => 'Kadaluarsa',
            'COMPLETED' => 'Selesai',
            'CANCELLED' => 'Dibatalkan',
            'all' => 'Semua',
            'deleted' => 'Dihapus (restore)',
        ] as $key => $label)
        <a href="{{ route('admin.bookings.index', ['status' => $key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium transition
                  {{ $filterStatus === $key ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Kode</th>
                    <th class="p-4 font-semibold">Pemesan</th>
                    <th class="p-4 font-semibold">No. WA</th>
                    <th class="p-4 font-semibold">Paket</th>
                    <th class="p-4 font-semibold">Tanggal / Sesi</th>
                    <th class="p-4 font-semibold">Peserta</th>
                    <th class="p-4 font-semibold">Total</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-mono text-xs">{{ $booking->booking_code }}</td>
                    <td class="p-4">{{ $booking->nama_lengkap }}</td>
                    <td class="p-4 font-mono text-xs">{{ $booking->no_whatsapp }}</td>
                    <td class="p-4">{{ $booking->paketWisata->nama ?? '-' }}</td>
                    <td class="p-4">{{ $booking->tanggal_kunjungan ? $booking->tanggal_kunjungan->format('d-m-Y') : '-' }} ({{ $booking->sesi }})</td>
                    <td class="p-4">{{ $booking->jumlah_peserta }} org</td>
                    <td class="p-4 font-medium">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                    <td class="p-4">
                        @php $badge = [
                            'PENDING_PAYMENT' => 'bg-yellow-100 text-yellow-700',
                            'PENDING_VERIFY' => 'bg-amber-100 text-amber-800',
                            'CONFIRMED' => 'bg-green-100 text-green-700',
                            'REJECTED' => 'bg-red-100 text-red-700',
                            'EXPIRED' => 'bg-gray-100 text-gray-500',
                            'COMPLETED' => 'bg-blue-100 text-blue-700',
                            'CANCELLED' => 'bg-gray-200 text-gray-500',
                        ][$booking->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $badge }}">{{ $booking->status }}</span>
                    </td>
                    <td class="p-4 flex items-center gap-2">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs hover:bg-blue-700 transition">Detail</a>
                        @if($booking->trashed())
                        <form method="POST" action="{{ route('admin.bookings.restore', $booking->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs hover:bg-emerald-700 transition">Restore</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}" class="inline" onsubmit="return confirm('Yakin hapus booking ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs hover:bg-red-700 transition">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="p-8 text-center text-gray-400">Tidak ada booking</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t">
            {{ $bookings->links() }}
        </div>
    </div>
</div>

{{-- MODAL EKSPOR EXCEL (FLEXIBLE PERIOD REKAP) --}}
<div id="exportModal" onclick="if(event.target === this) closeExportModal()" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden select-none">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 border border-slate-200" onclick="event.stopPropagation()">

        
        {{-- Modal Header --}}
        <div class="p-5 bg-gradient-to-r from-emerald-800 to-teal-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-300">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold">Rekap & Ekspor Laporan Excel</h3>
                    <p class="text-xs text-emerald-200">Unduh data pemesanan berformat .xlsx</p>
                </div>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Form Ekspor --}}
        <form method="GET" action="{{ route('admin.bookings.export') }}" class="p-5 space-y-4" onsubmit="setTimeout(closeExportModal, 800)">
            
            {{-- 1. Pilihan Periode Rekap --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Periode Rekap</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition text-center select-none period-option border-emerald-600 bg-emerald-50/50 text-emerald-900 font-semibold" data-type="all">
                        <input type="radio" name="period_type" value="all" checked class="hidden" onchange="togglePeriodFields(this.value)">
                        <span class="text-xs">Semua</span>
                        <span class="text-[10px] text-slate-400 font-normal">All Time</span>
                    </label>

                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-slate-200 hover:border-slate-300 cursor-pointer transition text-center select-none period-option text-slate-700" data-type="monthly">
                        <input type="radio" name="period_type" value="monthly" class="hidden" onchange="togglePeriodFields(this.value)">
                        <span class="text-xs font-semibold">Bulanan</span>
                        <span class="text-[10px] text-slate-400 font-normal">Pilih Bulan</span>
                    </label>

                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-slate-200 hover:border-slate-300 cursor-pointer transition text-center select-none period-option text-slate-700" data-type="yearly">
                        <input type="radio" name="period_type" value="yearly" class="hidden" onchange="togglePeriodFields(this.value)">
                        <span class="text-xs font-semibold">Tahunan</span>
                        <span class="text-[10px] text-slate-400 font-normal">1 Tahun Penuh</span>
                    </label>

                    <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-slate-200 hover:border-slate-300 cursor-pointer transition text-center select-none period-option text-slate-700" data-type="custom">
                        <input type="radio" name="period_type" value="custom" class="hidden" onchange="togglePeriodFields(this.value)">
                        <span class="text-xs font-semibold">Kustom</span>
                        <span class="text-[10px] text-slate-400 font-normal">Rentang Tgl</span>
                    </label>
                </div>
            </div>

            {{-- 2. Input Dinamis Sesuai Periode --}}
            
            {{-- Input Bulanan --}}
            <div id="monthlyFields" class="hidden bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ now()->month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            @for($y = now()->year + 1; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            {{-- Input Tahunan --}}
            <div id="yearlyFields" class="hidden bg-slate-50 p-3 rounded-xl border border-slate-200">
                <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Tahun</label>
                <select name="year_annual" id="yearAnnualInput" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                    @for($y = now()->year + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Input Kustom Tanggal --}}
            <div id="customFields" class="hidden bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- 3. Pengaturan Tambahan: Basis Tanggal & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Basis Tanggal</label>
                    <select name="date_basis" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="created_at">Tanggal Transaksi (Kapan Pesan)</option>
                        <option value="tanggal_kunjungan">Tanggal Kunjungan (Kapan Datang)</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Pilih patokan tanggal untuk penyaringan data</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Filter Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="all">Semua Status</option>
                        <option value="confirmed_completed">Terkonfirmasi & Selesai (Lunas)</option>
                        <option value="PENDING_VERIFY">Menunggu Verifikasi</option>
                        <option value="PENDING_PAYMENT">Menunggu Pembayaran</option>
                        <option value="CONFIRMED">Dikonfirmasi (Confirmed)</option>
                        <option value="COMPLETED">Selesai (Completed)</option>
                        <option value="REJECTED">Ditolak (Rejected)</option>
                        <option value="CANCELLED">Dibatalkan (Cancelled)</option>
                        <option value="EXPIRED">Kadaluarsa (Expired)</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Filter berdasarkan status pesanan</p>
                </div>
            </div>


            {{-- Modal Actions --}}
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeExportModal()" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-700 hover:bg-emerald-800 text-white shadow-md shadow-emerald-700/20 transition flex items-center gap-1.5 cursor-pointer">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Excel (.xlsx)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExportModal() {
        const modal = document.getElementById('exportModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeExportModal() {
        const modal = document.getElementById('exportModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }


    function togglePeriodFields(type) {
        // Update styling of radio option cards
        document.querySelectorAll('.period-option').forEach(el => {
            const isMatch = el.getAttribute('data-type') === type;
            if (isMatch) {
                el.classList.remove('border-slate-200', 'text-slate-700');
                el.classList.add('border-emerald-600', 'bg-emerald-50/50', 'text-emerald-900', 'font-semibold');
            } else {
                el.classList.remove('border-emerald-600', 'bg-emerald-50/50', 'text-emerald-900', 'font-semibold');
                el.classList.add('border-slate-200', 'text-slate-700');
            }
        });

        const monthlyBox = document.getElementById('monthlyFields');
        const yearlyBox = document.getElementById('yearlyFields');
        const customBox = document.getElementById('customFields');
        const yearAnnualInput = document.getElementById('yearAnnualInput');

        if (monthlyBox) monthlyBox.classList.add('hidden');
        if (yearlyBox) yearlyBox.classList.add('hidden');
        if (customBox) customBox.classList.add('hidden');

        if (type === 'monthly' && monthlyBox) {
            monthlyBox.classList.remove('hidden');
        } else if (type === 'yearly') {
            if (yearlyBox) yearlyBox.classList.remove('hidden');
            // If year annual input exists, ensure parameter name is synced
            if (yearAnnualInput) yearAnnualInput.setAttribute('name', 'year');
        } else if (type === 'custom' && customBox) {
            customBox.classList.remove('hidden');
        }

        if (type !== 'yearly' && yearAnnualInput) {
            yearAnnualInput.setAttribute('name', 'year_annual_inactive');
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeExportModal();
    });
</script>
@endsection
