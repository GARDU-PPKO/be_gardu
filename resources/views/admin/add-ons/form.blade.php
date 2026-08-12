@extends('admin.layouts.app')
@section('title', $addOn ? 'Edit Add-On' : 'Tambah Add-On')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $addOn ? 'Edit Add-On' : 'Tambah Add-On' }}</h2>
        <a href="{{ route('admin.add-ons.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $addOn ? route('admin.add-ons.update', $addOn->id) : route('admin.add-ons.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($addOn) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="nama" value="{{ old('nama', $addOn->nama ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <input type="text" name="kategori" value="{{ old('kategori', $addOn->kategori ?? '') }}" placeholder="Contoh: Makanan, Aktivitas" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Model Harga *</label>
                <select name="tipe_harga" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="per_unit" {{ old('tipe_harga', $addOn->tipe_harga ?? '') === 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                    <option value="per_orang" {{ old('tipe_harga', $addOn->tipe_harga ?? '') === 'per_orang' ? 'selected' : '' }}>Per Orang</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">Per Orang = dikali jumlah peserta; Per Unit = dihitung per satuan yang dipilih user.</p>
                @error('tipe_harga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                <input type="number" step="0.01" min="0" name="harga" value="{{ old('harga', $addOn->harga ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('harga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                @if(!empty($addOn->gambar))
                    <img src="{{ $addOn->gambar }}" alt="Gambar saat ini" class="w-full h-32 object-cover rounded-lg border border-gray-200 mb-2">
                @endif
                <input type="file" name="gambar" accept="image/*" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $addOn->aktif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="aktif" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <input type="number" min="1" name="urutan" value="{{ old('urutan', $addOn->urutan ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                <p class="text-xs text-gray-400 mt-1">Urutan tampil di daftar add-on (kecil = lebih dulu).</p>
                @error('urutan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $addOn->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.add-ons.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
