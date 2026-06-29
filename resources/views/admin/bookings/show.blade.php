@extends('admin.layouts.app')
@section('title', 'Detail Booking')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Detail Booking</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Kode Booking</span><p class="font-semibold font-mono">{{ $booking->kode_booking }}</p></div>
            <div><span class="text-gray-500">Status</span>
                <p><span class="px-2 py-1 text-xs rounded-full
                    @if($booking->status === 'confirmed') bg-green-100 text-green-700
                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">{{ $booking->status }}</span></p>
            </div>
            <div><span class="text-gray-500">Nama Pemesan</span><p class="font-semibold">{{ $booking->nama_pemesan }}</p></div>
            <div><span class="text-gray-500">No. WA</span><p class="font-semibold font-mono">{{ $booking->no_wa_pemesan }}</p></div>
            <div><span class="text-gray-500">Email</span><p>{{ $booking->email ?? '-' }}</p></div>
            <div><span class="text-gray-500">Kota Asal</span><p>{{ $booking->kota_asal ?? '-' }}</p></div>
            <div><span class="text-gray-500">Paket</span><p class="font-semibold">{{ $booking->package->nama ?? '-' }}</p></div>
            <div><span class="text-gray-500">Tanggal</span><p>{{ $booking->tanggal }}</p></div>
            <div><span class="text-gray-500">Sesi</span><p>{{ $booking->sesi }}</p></div>
            <div><span class="text-gray-500">Jumlah Peserta</span><p>{{ $booking->jumlah_peserta }} orang</p></div>
            <div><span class="text-gray-500">Total Harga</span><p class="font-bold text-lg">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p></div>
            <div><span class="text-gray-500">Catatan</span><p>{{ $booking->catatan ?? '-' }}</p></div>
        </div>

        @if($booking->bukti_bayar)
        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm">Bukti Bayar</span>
            <img src="{{ $booking->bukti_bayar }}" loading="lazy" class="mt-2 max-w-sm rounded-lg border">
        </div>
        @endif

        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Raw WA Text</span>
            <pre class="bg-gray-50 p-4 rounded-lg text-xs whitespace-pre-wrap font-mono">{{ $booking->raw_wa_text }}</pre>
        </div>
    </div>

    @if($booking->status === 'pending')
    <div class="flex gap-3">
        <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}">
            @csrf
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">Konfirmasi</button>
        </form>
        <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Yakin cancel?')">
            @csrf
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">Batalkan</button>
        </form>
    </div>
    @endif
</div>
@endsection
