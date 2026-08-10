@extends('admin.layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Bookings</h2>
            <p class="text-xs text-gray-500 mt-1">Default filter: menunggu verifikasi (FIFO)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.export') }}" class="px-4 py-2 bg-white border border-emerald-700 text-emerald-700 rounded-lg text-sm hover:bg-emerald-50 transition">Export Excel</a>
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
                    <td class="p-4">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                    <td class="p-4">
                        @php $badge = [
                            'PENDING_PAYMENT' => 'bg-yellow-100 text-yellow-700',
                            'PENDING_VERIFY' => 'bg-orange-100 text-orange-700',
                            'CONFIRMED' => 'bg-green-100 text-green-700',
                            'REJECTED' => 'bg-red-100 text-red-700',
                            'EXPIRED' => 'bg-gray-200 text-gray-600',
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
        <div class="p-4 border-t">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
