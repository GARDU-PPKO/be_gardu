@extends('admin.layouts.app')
@section('title', 'Detail Paket Wisata')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $package->nama }}</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.paket-wisata.edit', $package->id) }}" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Edit</a>
            <a href="{{ route('admin.paket-wisata.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800 self-center">← Kembali</a>
        </div>
    </div>

    @if($package->gambar)
    <img src="{{ $package->gambar }}" class="w-full max-h-64 object-cover rounded-xl shadow-sm">
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 text-sm">
        <div class="grid grid-cols-2 gap-4">
            <div><span class="text-gray-500">Kategori</span><p class="font-semibold capitalize">{{ $package->kategori }}</p></div>
            <div><span class="text-gray-500">Model Harga</span><p class="font-semibold">{{ $package->tipe_harga === 'per_orang_tier' ? 'Per Orang (Tier)' : 'Per Paket (Fixed)' }}</p></div>
            <div><span class="text-gray-500">Durasi</span><p>{{ $package->durasi ?? '-' }}</p></div>
            <div><span class="text-gray-500">Tag</span><p>{{ $package->tag ?? '-' }}</p></div>
            <div><span class="text-gray-500">Status</span><p>{{ $package->aktif ? 'Aktif' : 'Nonaktif' }}</p></div>
        </div>

        @if($package->tipe_harga === 'per_orang_tier')
        <div class="border-t pt-4">
            <span class="text-gray-500 block mb-2">Tier Harga</span>
            <div class="space-y-1">
                @forelse($package->tiers->sortByDesc('min_peserta') as $tier)
                <div class="flex justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                    <span>≥ {{ $tier->min_peserta }} orang</span>
                    <span class="font-semibold">Rp {{ number_format($tier->harga_per_orang, 0, ',', '.') }}/orang</span>
                </div>
                @empty
                <p class="text-gray-400">Belum ada tier</p>
                @endforelse
            </div>
        </div>
        @else
        <div class="border-t pt-4 grid grid-cols-2 gap-4">
            <div><span class="text-gray-500">Harga Paket</span><p class="font-bold text-lg">Rp {{ number_format($package->harga_paket, 0, ',', '.') }}</p></div>
            <div><span class="text-gray-500">Kapasitas / Unit</span><p>{{ $package->kapasitas_per_unit }} peserta</p></div>
        </div>
        @endif

        <div class="border-t pt-4">
            <span class="text-gray-500 block mb-2">Deskripsi</span>
            <p class="whitespace-pre-wrap">{{ $package->deskripsi ?? '-' }}</p>
        </div>

        <div class="border-t pt-4">
            <span class="text-gray-500 block mb-2">Fasilitas</span>
            @if($package->fasilitas)
            <ul class="list-disc list-inside space-y-1">
                @foreach($package->fasilitas as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ul>
            @else
            <p class="text-gray-400">Tidak ada</p>
            @endif
        </div>
    </div>
</div>
@endsection
