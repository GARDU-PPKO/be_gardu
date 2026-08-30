@extends('admin.layouts.app')
@section('title', $dusun ? 'Edit Dusun' : 'Tambah Dusun')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $dusun ? 'Edit Dusun' : 'Tambah Dusun' }}</h2>
        <a href="{{ route('admin.dusun.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    {{-- Main Form untuk data dusun --}}
    <form method="POST" action="{{ $dusun ? route('admin.dusun.update', $dusun->id) : route('admin.dusun.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($dusun) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="nama" value="{{ old('nama', $dusun->nama ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RW *</label>
                <input type="text" name="rw" value="{{ old('rw', $dusun->rw ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('rw') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hero Image {{ $dusun ? '' : '*' }}</label>
                @if(!empty($dusun->hero_img))
                    <img src="{{ $dusun->hero_img }}" alt="Hero image saat ini" class="w-full h-32 object-cover rounded-lg border border-gray-200 mb-2">
                @endif
                <input type="file" name="hero_img" accept="image/*" {{ $dusun ? '' : 'required' }} class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('hero_img') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thumbnail {{ $dusun ? '' : '*' }}</label>
                @if(!empty($dusun->thumbnail))
                    <img src="{{ $dusun->thumbnail }}" alt="Thumbnail saat ini" class="w-full h-32 object-cover rounded-lg border border-gray-200 mb-2">
                @endif
                <input type="file" name="thumbnail" accept="image/*" {{ $dusun ? '' : 'required' }} class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-2 sm:col-span-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $dusun->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
            <textarea name="deskripsi" rows="4" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $dusun->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Detail *</label>
            <textarea name="detail" rows="6" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('detail', $dusun->detail ?? '') }}</textarea>
            @error('detail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.dusun.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>

    {{-- Keunggulan --}}
    @if($dusun)
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-gray-800">Keunggulan</h3>

        @if($dusun->keunggulan->count() > 0)
        <div class="space-y-2">
            @foreach($dusun->keunggulan as $keunggulan)
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-600 flex-1">{{ $keunggulan->keunggulan }}</span>
                <form method="POST" action="{{ route('admin.dusun.keunggulan.destroy', [$dusun->id, $keunggulan->id]) }}" onsubmit="return confirm('Yakin hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-sm">Belum ada keunggulan</p>
        @endif

        <form method="POST" action="{{ route('admin.dusun.keunggulan.store', $dusun->id) }}" class="flex gap-2">
            @csrf
            <input type="text" name="keunggulan" placeholder="Nama keunggulan" required class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Tambah</button>
        </form>
    </div>
    @endif
</div>
@endsection
