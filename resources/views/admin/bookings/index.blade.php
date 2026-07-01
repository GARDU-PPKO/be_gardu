@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Booking</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola seluruh data pemesanan wisata Desa Getas</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.bookings.export', array_filter(request()->only(['status','tanggal_dari','tanggal_sampai']))) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-150 hover:shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.bookings.parse') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-150 hover:shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                </svg>
                Parse Text WA
            </a>
        </div>
    </div>

    {{-- ===== FILTER PANEL ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0" class="text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold text-gray-600">Filter Data</span>
            @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
            <span class="ml-auto text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Filter aktif</span>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.bookings.index') }}"
              class="px-5 py-4 flex flex-wrap items-end gap-4">
            {{-- Status --}}
            <div class="flex flex-col gap-1.5 min-w-[140px]">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                <select name="status"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition">
                    <option value="">Semua Status</option>
                    <option value="pending"   @selected(request('status') === 'pending')>⏳ Pending</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>✅ Confirmed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>❌ Cancelled</option>
                </select>
            </div>

            {{-- Tanggal Dari --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                       class="text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition">
            </div>

            {{-- Tanggal Sampai --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                       class="text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition">
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 pb-0.5">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan
                </button>
                @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('admin.bookings.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== TABEL ===== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-600">
                Daftar Booking
                @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
                <span class="text-xs text-gray-400 font-normal ml-1">(terfilter)</span>
                @endif
            </span>
            <span class="text-xs text-gray-400">{{ $bookings->total() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemesan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. WA</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Paket</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Total</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-100">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $booking->kode_booking }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-medium text-gray-800">{{ $booking->nama_pemesan ?: '-' }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">
                            {{ $booking->no_wa_pemesan ?: '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-gray-700">
                            {{ $booking->package->nama ?? '<span class="text-gray-400">-</span>' }}
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">
                            {{ $booking->tanggal?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-semibold text-gray-700">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($booking->status === 'confirmed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Confirmed
                                </span>
                            @elseif($booking->status === 'cancelled')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-600 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline transition">
                                    Detail
                                </a>
                                @if($booking->status === 'pending')
                                <span class="text-gray-200">|</span>
                                <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 transition">
                                        ✓ Confirm
                                    </button>
                                </form>
                                <span class="text-gray-200">|</span>
                                <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" class="inline"
                                      onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 transition">
                                        ✕ Cancel
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;opacity:0.3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm font-medium">Belum ada data booking</p>
                                <p class="text-xs">Coba ubah filter atau tambah booking baru via Parse Text WA</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
