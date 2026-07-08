@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Manajemen Booking</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola seluruh data pemesanan wisata Desa Getas</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.parse') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                </svg>
                Parse Text WA
            </a>
            <a href="{{ route('admin.bookings.export', array_filter(request()->only(['status','tanggal_dari','tanggal_sampai']))) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0d3b2e] hover:bg-[#092b21] text-white text-sm font-medium rounded-lg shadow-sm hover:shadow transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200/80 rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold text-gray-800">Filter Data</span>
            @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
            <span class="ml-auto text-[11px] bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-0.5 rounded font-medium">Aktif</span>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.bookings.index') }}"
              class="px-6 py-5 flex flex-wrap items-end gap-5 bg-gray-50/30 rounded-b-xl">
            <div class="flex flex-col gap-2 min-w-[150px]">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</label>
                <select name="status"
                        class="text-sm border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#0d3b2e]/20 focus:border-[#0d3b2e] transition-shadow">
                    <option value="">Semua Status</option>
                    <option value="pending"   @selected(request('status') === 'pending')>Pending</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mulai Tanggal</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                       class="text-sm border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#0d3b2e]/20 focus:border-[#0d3b2e] transition-shadow">
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                       class="text-sm border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#0d3b2e]/20 focus:border-[#0d3b2e] transition-shadow">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0d3b2e] hover:bg-[#092b21] text-white text-sm font-medium rounded-lg transition-colors">
                    Terapkan
                </button>
                @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('admin.bookings.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200/80 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-800">
                Daftar Booking
            </span>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">{{ $bookings->total() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-200 bg-gray-50/50">
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Pemesan</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider">No. WA</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Paket</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">Total</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-3.5 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-6 py-4 align-middle">
                            <span class="font-mono text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2 py-1 rounded">
                                {{ $booking->kode_booking }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <span class="font-medium text-gray-900">{{ $booking->nama_pemesan ?: '-' }}</span>
                        </td>
                        <td class="px-6 py-4 align-middle font-mono text-xs text-gray-500">
                            {{ $booking->no_wa_pemesan ?: '-' }}
                        </td>
                        <td class="px-6 py-4 align-middle text-gray-600">
                            {{ $booking->package->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 align-middle text-gray-500 text-sm">
                            {{ $booking->tanggal?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 align-middle text-right font-medium text-gray-900">
                            Rp {{ number_format($booking->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 align-middle text-center">
                            @if($booking->status === 'confirmed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Confirmed
                                </span>
                            @elseif($booking->status === 'cancelled')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-red-50 text-red-700 border border-red-200 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                                   title="Detail Booking">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($booking->status === 'pending')
                                <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                                            title="Konfirmasi Booking">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" class="inline"
                                      onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors"
                                            title="Batalkan Booking">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-500">Belum ada data booking</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
