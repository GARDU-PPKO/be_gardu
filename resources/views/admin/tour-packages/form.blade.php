@extends('admin.layouts.app')
@section('title', $package ? 'Edit Paket Wisata' : 'Tambah Paket Wisata')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $package ? 'Edit Paket Wisata' : 'Tambah Paket Wisata' }}</h2>
        <a href="{{ route('admin.tour-packages.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $package ? route('admin.tour-packages.update', $package->id) : route('admin.tour-packages.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($package) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="nama" value="{{ old('nama', $package->nama ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                <input type="number" step="0.01" name="harga" value="{{ old('harga', $package->harga ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('harga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                <select name="satuan" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="orang" {{ old('satuan', $package->satuan ?? '') === 'orang' ? 'selected' : '' }}>Per Orang</option>
                    <option value="grup" {{ old('satuan', $package->satuan ?? '') === 'grup' ? 'selected' : '' }}>Per Grup</option>
                </select>
                @error('satuan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi *</label>
                <input type="text" name="durasi" value="{{ old('durasi', $package->durasi ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('durasi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                <input type="text" name="tag" value="{{ old('tag', $package->tag ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('tag') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar URL *</label>
                <input type="text" name="gambar" value="{{ old('gambar', $package->gambar ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Participants</label>
                <input type="number" name="min_participants" value="{{ old('min_participants', $package->min_participants ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('min_participants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Participants</label>
                <input type="number" name="max_participants" value="{{ old('max_participants', $package->max_participants ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('max_participants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
            <textarea name="deskripsi" rows="5" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $package->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.tour-packages.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>

    @if($package)
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-gray-800">Includes</h3>

        @if($package->includes->count() > 0)
        <div class="space-y-2">
            @foreach($package->includes as $include)
            <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                <span class="text-gray-600">{{ $include->item }}</span>
                <form method="POST" action="{{ route('admin.tour-packages.includes.destroy', [$package->id, $include->id]) }}" onsubmit="return confirm('Yakin hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-sm">Belum ada include</p>
        @endif

        <form method="POST" action="{{ route('admin.tour-packages.includes.store', $package->id) }}" class="flex gap-2">
            @csrf
            <input type="text" name="item" placeholder="Nama include" required class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Tambah</button>
        </form>
    </div>
    @endif
</div>
@endsection
