@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Bookings</h2>
        <a href="{{ route('admin.bookings.parse') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">+ Parse Text WA</a>
    </div>

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
                    <td class="p-4">{{ $booking->tanggal }}</td>
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
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
