@extends('admin.layouts.app')
@section('title', 'Detail Booking')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Detail Booking</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-gray-500 text-sm">Kode Booking</span>
                <p class="font-bold font-mono">{{ $booking->booking_code }}</p>
            </div>
            @php $badge = [
                'PENDING_PAYMENT' => 'bg-yellow-100 text-yellow-700',
                'PENDING_VERIFY' => 'bg-orange-100 text-orange-700',
                'CONFIRMED' => 'bg-green-100 text-green-700',
                'REJECTED' => 'bg-red-100 text-red-700',
                'EXPIRED' => 'bg-gray-200 text-gray-600',
                'COMPLETED' => 'bg-blue-100 text-blue-700',
                'CANCELLED' => 'bg-gray-200 text-gray-500',
            ][$booking->status] ?? 'bg-gray-100 text-gray-600'; @endphp
            <span class="px-3 py-1 text-xs rounded-full {{ $badge }}">{{ $booking->status }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Nama Lengkap</span><p class="font-semibold">{{ $booking->nama_lengkap }}</p></div>
            <div><span class="text-gray-500">No. WA</span><p class="font-semibold font-mono">{{ $booking->no_whatsapp }}</p></div>
            <div><span class="text-gray-500">Alamat</span><p>{{ $booking->alamat }}</p></div>
            <div><span class="text-gray-500">Kontak Darurat</span><p>{{ $booking->kontak_darurat_nama }} ({{ $booking->kontak_darurat_telp }})</p></div>
            <div><span class="text-gray-500">Paket</span><p class="font-semibold">{{ $booking->paketWisata->nama ?? '-' }}</p></div>
            <div><span class="text-gray-500">Tanggal Kunjungan</span><p>{{ $booking->tanggal_kunjungan ? $booking->tanggal_kunjungan->format('d-m-Y') : '-' }} — {{ $booking->sesi }}</p></div>
            <div><span class="text-gray-500">Jumlah Peserta</span><p>{{ $booking->jumlah_peserta }} orang</p></div>
            @if($booking->addOns->count() > 0)
            <div class="col-span-2">
                <span class="text-gray-500 block mb-1">Add-On</span>
                <div class="space-y-1">
                    @foreach($booking->addOns as $addOn)
                    <div class="flex justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                        <span>{{ $addOn->nama }} x{{ $addOn->pivot->qty }}</span>
                        <span class="font-semibold">Rp {{ number_format($addOn->pivot->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <div>
                <span class="text-gray-500">Nominal yang Harus Dibayar</span>
                <p class="font-bold text-lg">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p>
            </div>
            @if($booking->nominal_transfer !== null)
            <div><span class="text-gray-500">Nominal Transfer</span><p class="font-semibold">Rp {{ number_format($booking->nominal_transfer, 0, ',', '.') }}</p></div>
            @endif
            @if($booking->metode_pembayaran)
            <div><span class="text-gray-500">Metode</span><p>{{ $booking->metode_pembayaran }}</p></div>
            @endif
            @if($booking->rejected_reason)
            <div class="col-span-2"><span class="text-gray-500">Alasan Penolakan</span><p class="font-semibold text-red-600">{{ $booking->rejected_reason }}</p></div>
            @endif
            <div><span class="text-gray-500">Catatan</span><p>{{ $booking->notes ?? '-' }}</p></div>
            @if($booking->verified_at)
            <div><span class="text-gray-500">Diverifikasi</span><p>{{ $booking->verified_at->format('d-m-Y H:i') }} oleh {{ $booking->logs->first()->admin->nama ?? 'Admin' }}</p></div>
            @endif
        </div>

        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Bukti Pembayaran</span>
            @if($booking->bukti_pembayaran_path)
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.bookings.bukti', $booking->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm underline">Lihat bukti pembayaran</a>
                @if(str_ends_with($booking->bukti_pembayaran_path, '.webp') || str_ends_with($booking->bukti_pembayaran_path, '.png') || str_ends_with($booking->bukti_pembayaran_path, '.jpg') || str_ends_with($booking->bukti_pembayaran_path, '.jpeg'))
                <img src="{{ route('admin.bookings.bukti', $booking->id) }}" loading="lazy" class="mt-2 max-w-sm rounded-lg border">
                @endif
            </div>
            @else
            <p class="text-gray-400 text-sm">Belum ada bukti pembayaran.</p>
            @endif
        </div>

        @if($booking->logs->count() > 0)
        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Riwayat (Audit Trail)</span>
            <div class="space-y-2">
                @foreach($booking->logs as $log)
                <div class="text-xs flex gap-3 bg-gray-50 rounded-lg px-3 py-2">
                    <span class="font-mono text-gray-400">{{ $log->created_at->format('d-m-Y H:i') }}</span>
                    <span class="font-semibold uppercase">{{ $log->action }}</span>
                    <span class="text-gray-600">{{ $log->detail }}</span>
                    <span class="text-gray-400 ml-auto">{{ $log->admin->nama ?? 'User' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if($booking->status === 'PENDING_VERIFY' || $booking->status === 'PENDING_PAYMENT')
    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}">
            @csrf
            <button type="submit" class="w-full px-6 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition"
                onclick="return confirm('Konfirmasi booking {{ $booking->booking_code }}? WA konfirmasi akan dikirim ke user.')">
                ✓ Konfirmasi (Valid)
            </button>
        </form>
        <form method="POST" action="{{ route('admin.bookings.reject', $booking->id) }}" class="bg-white rounded-xl shadow-sm p-4 space-y-2">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Alasan Penolakan *</label>
            <select name="rejected_reason" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none text-sm">
                <option value="">-- Pilih Alasan --</option>
                <option value="Nominal tidak sesuai">Nominal tidak sesuai</option>
                <option value="Bukti tidak jelas/buram">Bukti tidak jelas/buram</option>
                <option value="Bukan bukti transfer">Bukan bukti transfer</option>
                <option value="Nama pengirim tidak cocok">Nama pengirim tidak cocok</option>
                <option value="Tanggal transfer tidak sesuai">Tanggal transfer tidak sesuai</option>
            </select>
            <input type="text" name="rejected_reason_custom" placeholder="Alasan lain (opsional)" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none text-sm">
            <button type="submit" class="w-full px-6 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition"
                onclick="return confirm('Tolak booking {{ $booking->booking_code }}? WA alasan penolakan akan dikirim.')">
                ✕ Tolak (Tidak Valid)
            </button>
        </form>
    </div>
    @endif

    @if(!$booking->trashed())
    <form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}" class="text-center" onsubmit="return confirm('Hapus booking ini? (soft delete, bisa di-restore dari filter Dihapus)')">
        @csrf @method('DELETE')
        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus Booking</button>
    </form>
    @else
    <form method="POST" action="{{ route('admin.bookings.restore', $booking->id) }}" class="text-center">
        @csrf
        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800">Restore Booking</button>
    </form>
    @endif
</div>
@endsection
