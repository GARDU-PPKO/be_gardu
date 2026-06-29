@extends('admin.layouts.app')
@section('title', $package->nama)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $package->nama }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.tour-packages.edit', $package->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Edit</a>
            <a href="{{ route('admin.tour-packages.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Nama</span><p class="font-semibold">{{ $package->nama }}</p></div>
            <div><span class="text-gray-500">Harga</span><p class="font-semibold">Rp {{ number_format($package->harga, 0, ',', '.') }} / {{ $package->satuan }}</p></div>
            <div><span class="text-gray-500">Durasi</span><p>{{ $package->durasi }}</p></div>
            <div><span class="text-gray-500">Tag</span><p>{{ $package->tag ?? '-' }}</p></div>
            <div><span class="text-gray-500">Min Peserta</span><p>{{ $package->min_participants ?? '-' }}</p></div>
            <div><span class="text-gray-500">Max Peserta</span><p>{{ $package->max_participants ?? '-' }}</p></div>
            <div><span class="text-gray-500">Status</span>
                <p><span class="px-2 py-1 text-xs rounded-full {{ $package->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span></p>
            </div>
        </div>

        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm">Deskripsi</span>
            <p class="mt-1 text-sm">{{ $package->deskripsi }}</p>
        </div>

        @if($package->includes->count() > 0)
        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Includes</span>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($package->includes as $inc)
                <li>{{ $inc->item }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
