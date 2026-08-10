@extends('admin.layouts.app')
@section('title', $package ? 'Edit Paket Wisata' : 'Tambah Paket Wisata')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $package ? 'Edit Paket Wisata' : 'Tambah Paket Wisata' }}</h2>
        <a href="{{ route('admin.paket-wisata.index') }}" class="text-sm text-emerald-700 hover:text-emerald-800">← Kembali</a>
    </div>

    <form method="POST" action="{{ $package ? route('admin.paket-wisata.update', $package->id) : route('admin.paket-wisata.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf
        @if($package) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="nama" value="{{ old('nama', $package->nama ?? '') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                <select name="kategori" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="tubing" {{ old('kategori', $package->kategori ?? '') === 'tubing' ? 'selected' : '' }}>Tubing / Sungai</option>
                    <option value="camping" {{ old('kategori', $package->kategori ?? '') === 'camping' ? 'selected' : '' }}>Camping</option>
                </select>
                @error('kategori') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Model Harga *</label>
                <select name="tipe_harga" required class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                    <option value="per_orang_tier" {{ old('tipe_harga', $package->tipe_harga ?? '') === 'per_orang_tier' ? 'selected' : '' }}>Per Orang (Tier)</option>
                    <option value="per_paket_fixed" {{ old('tipe_harga', $package->tipe_harga ?? '') === 'per_paket_fixed' ? 'selected' : '' }}>Per Paket (Fixed)</option>
                </select>
                @error('tipe_harga') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi</label>
                <input type="text" name="durasi" value="{{ old('durasi', $package->durasi ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('durasi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                <input type="text" name="tag" value="{{ old('tag', $package->tag ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('tag') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar URL</label>
                <input type="text" name="gambar" value="{{ old('gambar', $package->gambar ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('gambar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div id="harga_paket_wrap">
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Paket (fixed)</label>
                <input type="number" step="0.01" name="harga_paket" value="{{ old('harga_paket', $package->harga_paket ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('harga_paket') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div id="kapasitas_wrap">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas / Unit (fixed)</label>
                <input type="number" min="1" name="kapasitas_per_unit" value="{{ old('kapasitas_per_unit', $package->kapasitas_per_unit ?? '') }}" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                @error('kapasitas_per_unit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $package->aktif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="aktif" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="5" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('deskripsi', $package->deskripsi ?? '') }}</textarea>
            @error('deskripsi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas (satu per baris)</label>
            <textarea name="fasilitas" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">{{ old('fasilitas', $package ? implode("\n", $package->fasilitas ?? []) : '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Contoh: Pelampung & helm, Pemandu lokal, Makan malam</p>
            @error('fasilitas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Simpan</button>
            <a href="{{ route('admin.paket-wisata.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>

    @if($package)
    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-gray-800">Tier Harga (hanya untuk model Per Orang)</h3>

        @if($package->tipe_harga === 'per_orang_tier')
            @if($package->tiers->count() > 0)
            <div class="space-y-2">
                @foreach($package->tiers as $tier)
                <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                    <span class="text-gray-600">≥ {{ $tier->min_peserta }} orang → Rp {{ number_format($tier->harga_per_orang, 0, ',', '.') }}/orang</span>
                    <form method="POST" action="{{ route('admin.paket-wisata.tiers.destroy', [$package->id, $tier->id]) }}" onsubmit="return confirm('Yakin hapus tier ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-400 text-sm">Belum ada tier. Tambahkan minimal 1 tier (yang paling kecil min_peserta-nya jadi harga dasar).</p>
            @endif

            <form method="POST" action="{{ route('admin.paket-wisata.tiers.store', $package->id) }}" class="flex gap-2 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Min Peserta</label>
                    <input type="number" name="min_peserta" required min="1" class="w-32 px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Harga / Orang</label>
                    <input type="number" name="harga_per_orang" required min="0" class="w-40 px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 outline-none text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm hover:bg-emerald-800 transition">Tambah</button>
            </form>
        @else
            <p class="text-gray-400 text-sm">Paket model fixed tidak memakai tier — harga sudah diisi di kolom "Harga Paket".</p>
        @endif
    </div>
    @endif
</div>

<script>
(function () {
    const tipeSelect = document.querySelector('[name=tipe_harga]');
    const wrapHarga = document.getElementById('harga_paket_wrap');
    const wrapKapasitas = document.getElementById('kapasitas_wrap');

    function toggle() {
        const isFixed = tipeSelect.value === 'per_paket_fixed';
        wrapHarga.style.display = isFixed ? '' : 'none';
        wrapKapasitas.style.display = isFixed ? '' : 'none';
    }

    if (tipeSelect) {
        tipeSelect.addEventListener('change', toggle);
        toggle();
    }
})();
</script>
@endsection
