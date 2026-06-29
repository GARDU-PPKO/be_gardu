@extends('admin.layouts.app')
@section('title', $product ? 'Edit Produk UMKM' : 'Tambah Produk UMKM')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $product ? 'Edit Produk UMKM' : 'Tambah Produk UMKM' }}</h2>
        <a href="{{ route('admin.umkm-products.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $product ? route('admin.umkm-products.update', $product->id) : route('admin.umkm-products.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($product) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="nama" value="{{ old('nama', $product->nama ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                <select name="kategori" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="Makanan" {{ old('kategori', $product->kategori ?? '') === 'Makanan' ? 'selected' : '' }}>Makanan</option>
                    <option value="Kerajinan" {{ old('kategori', $product->kategori ?? '') === 'Kerajinan' ? 'selected' : '' }}>Kerajinan</option>
                    <option value="Pertanian" {{ old('kategori', $product->kategori ?? '') === 'Pertanian' ? 'selected' : '' }}>Pertanian</option>
                    <option value="Oleh-Oleh" {{ old('kategori', $product->kategori ?? '') === 'Oleh-Oleh' ? 'selected' : '' }}>Oleh-Oleh</option>
                </select>
                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                <input type="number" step="0.01" name="harga" value="{{ old('harga', $product->harga ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('harga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WA Penjual *</label>
                <input type="text" name="no_wa_penjual" value="{{ old('no_wa_penjual', $product->no_wa_penjual ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('no_wa_penjual') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar URL *</label>
                <input type="text" name="gambar" value="{{ old('gambar', $product->gambar ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
            <textarea name="deskripsi" rows="5" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.umkm-products.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
