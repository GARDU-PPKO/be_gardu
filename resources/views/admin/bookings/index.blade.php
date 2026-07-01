@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Bookings</h2>
        <div class="flex items-center gap-2">
            {{-- Tombol Export Excel (tanda spidol merah) --}}
            <a href="{{ route('admin.bookings.export', array_filter(request()->only(['status','tanggal_dari','tanggal_sampai']))) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('admin.bookings.parse') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">
                + Parse Text WA
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.bookings.index') }}"
          class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <option value="">Semua</option>
                <option value="pending"   @selected(request('status') === 'pending')>Pending</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal Sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <button type="submit"
                class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">
            Filter
        </button>
        @if(request()->hasAny(['status','tanggal_dari','tanggal_sampai']))
        <a href="{{ route('admin.bookings.index') }}"
           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b bg-gray-50">
                    <th class="p-4 font-semibold">Kode</th>
                    <th class="p-4 font-semibold">Pemesan</th>
                    <th class="p-4 font-semibold">No. WA</th>
                    <th class="p-4 font-semibold">Paket</th>
                    <th class="p-4 font-semibold">Tanggal</th>
                    <th class="p-4 font-semibold">Total</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-4 font-mono text-xs">{{ $booking->kode_booking }}</td>
                    <td class="p-4">{{ $booking->nama_pemesan }}</td>
                    <td class="p-4 font-mono text-xs">{{ $booking->no_wa_pemesan }}</td>
                    <td class="p-4">{{ $booking->package->nama ?? '-' }}</td>
                    <td class="p-4">{{ $booking->tanggal?->format('d/m/Y') }}</td>
                    <td class="p-4">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($booking->status === 'confirmed') bg-green-100 text-green-700
                            @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ $booking->status }}
                        </span>
                    </td>
                    <td class="p-4 flex gap-2">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                        @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 text-xs">Confirm</button>
                        </form>
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" class="inline" onsubmit="return confirm('Yakin cancel?')">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Cancel</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="p-8 text-center text-gray-400">Belum ada booking</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
