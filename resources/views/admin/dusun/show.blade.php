@extends('admin.layouts.app')
@section('title', $dusun->nama)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $dusun->nama }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.dusun.edit', $dusun->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Edit</a>
            <a href="{{ route('admin.dusun.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Nama</span><p class="font-semibold">{{ $dusun->nama }}</p></div>
            <div><span class="text-gray-500">RW</span><p class="font-semibold">{{ $dusun->rw }}</p></div>
            <div><span class="text-gray-500">Jumlah RT</span><p>{{ $dusun->jumlah_rt }}</p></div>
            <div><span class="text-gray-500">Jumlah Penduduk</span><p>{{ $dusun->jumlah_penduduk }}</p></div>
            <div><span class="text-gray-500">Luas Wilayah</span><p>{{ $dusun->luas_wilayah }}</p></div>
            <div><span class="text-gray-500">Status</span>
                <p><span class="px-2 py-1 text-xs rounded-full {{ $dusun->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $dusun->is_active ? 'Aktif' : 'Nonaktif' }}</span></p>
            </div>
        </div>

        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm">Deskripsi</span>
            <p class="mt-1 text-sm">{{ $dusun->deskripsi }}</p>
        </div>

        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm">Detail</span>
            <p class="mt-1 text-sm whitespace-pre-wrap">{{ $dusun->detail }}</p>
        </div>

        @if($dusun->galleries->count() > 0)
        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Galeri</span>
            <div class="space-y-1">
                @foreach($dusun->galleries as $gallery)
                <p class="text-sm font-mono">{{ $gallery->image_url }}</p>
                @endforeach
            </div>
        </div>
        @endif

        @if($dusun->keunggulan->count() > 0)
        <div class="pt-4 border-t">
            <span class="text-gray-500 text-sm block mb-2">Keunggulan</span>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($dusun->keunggulan as $k)
                <li>{{ $k->keunggulan }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
